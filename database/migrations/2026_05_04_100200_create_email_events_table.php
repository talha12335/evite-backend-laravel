<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->default('sendgrid');
            $table->string('event_type', 50);
            $table->string('email')->nullable();
            $table->foreignId('invitation_id')->nullable()->constrained('invitations')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete()->cascadeOnUpdate();
            $table->string('message_id')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['provider', 'event_type']);
            $table->index(['email', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_events');
    }
}
