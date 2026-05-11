<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\TestMail;
use App\Models\Guest;
use Exception;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;


class GuestController extends Controller
{
    public function index()
    {
        $guest = Guest::all();
        if (count($guest) > 0) {
            $response = [
                'message' => count($guest) . ' Guest Found',
                'status' => 1,
                'guest_data' => $guest
            ];
        } else {
            $response = [
                'message' => count($guest) . ' Guest Found',
                'status' => 0,
            ];
        }
        return response()->json($response, 200);
    }


    // send email and store data
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'guestEmail' => 'required|array',
            'guestEmail.*' => 'string|email',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 200);
        }

        DB::beginTransaction();
        try {
            $validatedData = $validate->validated();
            $guest = Guest::where('invitation_id', $request->id)->exists();
            if ($guest) {
                // If guest exists, update their email
                $guest = Guest::where('invitation_id', $request->id)->first();
                $guest->guestEmail = json_encode($validatedData['guestEmail']);
                $guest->save();
            } else {
                //Else Add Email
                $guest = new Guest();
                $guest->guestEmail = json_encode($validatedData['guestEmail']);
                $guest->invitation_id = $request->id;
                $guest->save();
            }


            //fetch guest data:
            $guest = Guest::select("guestEmail")
                ->where('invitation_id', $request->id)
                ->first();

            if (!$guest) {
                throw new Exception('Guest not found');
            }

            // fetch images from invitation
            $invitation = Invitation::select("image")
                ->where('id', $request->id)
                ->first();

            if (!$invitation) {
                throw new Exception('Invitation not found');
            }

            $invitation->image = url('uploads/' . $invitation->image);

            $guest_emails = json_decode($guest->guestEmail);

            DB::commit();

            // hit api for email sending using brevo
            $brevoApiKey = env('BREVO_API_KEY');
            $brevoFromName = env('BREVO_FROM_NAME', 'Honest Art');
            $brevoFromEmail = env('BREVO_FROM_EMAIL', 'hello@honestart.com');

            if (!$brevoApiKey) {
                throw new Exception('BREVO_API_KEY is not configured');
            }

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "https://api.brevo.com/v3/smtp/email");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);

            $headers = array();
            $headers[] = "accept: application/json";
            $headers[] = "api-key: " . $brevoApiKey;
            $headers[] = "content-type: application/json";
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $recipients = [];
            foreach ($guest_emails as $email) {
                $recipients[] = ['email' => $email, 'name' => 'Receiver Name'];
            }

            $data = array(
                "sender" => array(
                    "name" => $brevoFromName,
                    "email" => $brevoFromEmail
                ),
                "to" => $recipients,
                "subject" => "You're Invited!",
                "htmlContent" => "<html><head></head><body><img src='$invitation->image' alt='image'></body></html>"
            );

            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }

            curl_close($ch);

            $response = [
                'message' => 'Guest Added Successfully, Email Sent',
                'status' => 1,
                'guest_data' => $guest
            ];
            $response_code = 200;

        } catch (Exception $e) {
            DB::rollBack();
            $response = [
                'message' => 'Internal Server Error',
                'status' => 0,
                'error_message' => $e->getMessage()
            ];
            $response_code = 500;
        }

        return response()->json($response, $response_code);
    }


    public function show($id)
    {
        $guest = Guest::where("invitation_id", $id)->get();
        if (count($guest) > 0) {
            $response = [
                'message' => "Guest Found Successfully",
                'status' => 1,
                'guest_data' => $guest
            ];
        } else {
            $response = [
                'message' => 'No Guest is available against id = ' . $id,
                'status' => 0,
            ];
        }
        return response()->json($response, 200);
    }


    public function destroy($id)
    {
        $guest = Guest::find($id);
        if ($guest) {
            DB::beginTransaction();
            try {
                $guest->delete();
                DB::commit();
                $response = [
                    'message' => 'Guest Delete Successfully',
                    'status' => 1
                ];
                $response_code = 200;
            } catch (\Exception $e) {
                DB::rollBack();
                $response = [
                    'message' => 'Internal Server Error',
                    'status' => 0,
                    'error_message' => $e->getMessage()
                ];
                $response_code = 500;
            }
        } else {
            $response = [
                'message' => 'No Guest Found',
                'status' => 0
            ];
            $response_code = 200;
        }
        return response()->json($response, $response_code);
    }

}
