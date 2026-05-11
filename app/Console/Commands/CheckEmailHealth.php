<?php

namespace App\Console\Commands;

use App\Models\EmailEvent;
use App\Models\SystemAlert;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckEmailHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:health-check {--dry-run : Evaluate and print metrics without sending emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks bounce and spam complaint rates and creates operational alerts.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $windowHours = (int) env('EMAIL_ALERT_WINDOW_HOURS', 24);
        $bounceThreshold = (float) env('EMAIL_BOUNCE_ALERT_THRESHOLD', 3.0);
        $spamThreshold = (float) env('EMAIL_SPAM_ALERT_THRESHOLD', 0.2);
        $recipientsRaw = env('EMAIL_ALERT_RECIPIENTS', env('MAIL_FROM_ADDRESS'));

        $from = Carbon::now()->subHours($windowHours);

        $metrics = EmailEvent::query()
            ->selectRaw("SUM(CASE WHEN event_type = 'delivered' THEN 1 ELSE 0 END) AS delivered")
            ->selectRaw("SUM(CASE WHEN event_type = 'bounce' THEN 1 ELSE 0 END) AS bounce")
            ->selectRaw("SUM(CASE WHEN event_type = 'spamreport' THEN 1 ELSE 0 END) AS spamreport")
            ->where('created_at', '>=', $from)
            ->first();

        $delivered = max((int) ($metrics->delivered ?? 0), 1);
        $bounce = (int) ($metrics->bounce ?? 0);
        $spam = (int) ($metrics->spamreport ?? 0);

        $bounceRate = ($bounce / $delivered) * 100;
        $spamRate = ($spam / $delivered) * 100;

        $this->line('Email Health Window: last ' . $windowHours . 'h');
        $this->line('Delivered: ' . $delivered);
        $this->line('Bounce: ' . $bounce . ' (' . number_format($bounceRate, 2) . '%)');
        $this->line('Spam: ' . $spam . ' (' . number_format($spamRate, 2) . '%)');

        $alertsToCreate = [];

        if ($bounceRate >= $bounceThreshold) {
            $alertsToCreate[] = [
                'code' => 'bounce_rate_high',
                'severity' => 'critical',
                'title' => 'Bounce Rate Alert',
                'message' => 'Bounce rate reached ' . number_format($bounceRate, 2) . '% in the last ' . $windowHours . ' hours.',
                'meta' => [
                    'window_hours' => $windowHours,
                    'delivered' => $delivered,
                    'bounce' => $bounce,
                    'rate' => round($bounceRate, 2),
                    'threshold' => $bounceThreshold,
                ],
            ];
        }

        if ($spamRate >= $spamThreshold) {
            $alertsToCreate[] = [
                'code' => 'spam_rate_high',
                'severity' => 'critical',
                'title' => 'Spam Complaint Alert',
                'message' => 'Spam complaint rate reached ' . number_format($spamRate, 2) . '% in the last ' . $windowHours . ' hours.',
                'meta' => [
                    'window_hours' => $windowHours,
                    'delivered' => $delivered,
                    'spamreport' => $spam,
                    'rate' => round($spamRate, 2),
                    'threshold' => $spamThreshold,
                ],
            ];
        }

        if (empty($alertsToCreate)) {
            $this->info('No threshold breach detected.');
            return 0;
        }

        foreach ($alertsToCreate as $alert) {
            $alreadyActive = SystemAlert::where('code', $alert['code'])
                ->where('is_resolved', false)
                ->exists();

            if ($alreadyActive) {
                $this->warn('Skipping duplicate active alert: ' . $alert['code']);
                continue;
            }

            $createdAlert = SystemAlert::create([
                'code' => $alert['code'],
                'severity' => $alert['severity'],
                'title' => $alert['title'],
                'message' => $alert['message'],
                'meta' => $alert['meta'],
                'detected_at' => now(),
            ]);

            $this->warn('Created alert: #' . $createdAlert->id . ' ' . $createdAlert->title);

            if ($this->option('dry-run')) {
                continue;
            }

            $recipients = collect(explode(',', (string) $recipientsRaw))
                ->map(function ($value) {
                    return trim($value);
                })
                ->filter()
                ->values();

            if ($recipients->isEmpty()) {
                $this->warn('No EMAIL_ALERT_RECIPIENTS configured. Skipping email notification.');
                continue;
            }

            foreach ($recipients as $email) {
                Mail::raw(
                    "{$createdAlert->title}\n\n{$createdAlert->message}\n\nCode: {$createdAlert->code}\nDetected At: {$createdAlert->detected_at}",
                    function ($message) use ($email, $createdAlert) {
                        $message->to($email)
                            ->subject('[Evite Health Alert] ' . $createdAlert->title);
                    }
                );
            }
        }

        return 0;
    }
}
