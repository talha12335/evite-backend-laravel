<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\adminMail;
use App\Models\Invitation;
use App\Models\Location;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class InvitationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Fetch invitations for the user
        $invitations = Invitation::with('location')
            ->where("user_id", $request->user_id)
            ->orderBy('id', 'desc')
            ->get();
        foreach ($invitations as $invitation) {

            // Fetch the related template
            $template = Template::find($invitation->template_id);
            $event_name = $template ? $template->event_name : '';
            // Add the event_name as an 'event' property to the invitation object
            $invitation->event_name = $event_name;
            // Initialize image base64 URL
            $imageBase64 = null;

            if ($template) {
                $imagePath = 'uploads/' . $template->image;

                if (file_exists($imagePath)) {
                    $imageData = file_get_contents($imagePath);

                    // Get the mime type of the image
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->file($imagePath);

                    // Encode the image to base64
                    $base64 = base64_encode($imageData);

                    // Create the base64 image URL
                    $imageBase64 = 'data:' . $mimeType . ';base64,' . $base64;
                }

                // Assign image and base URL to invitation object

                $invitation->image_url = url('uploads/' . $invitation->image);
                //                $invitation->template_image = url('uploads/' . $template->image);;
                $invitation->template_base_url = $imageBase64;
            }

            $invitation->location_details = $invitation->location;
        }
        if (count($invitations) > 0) {
            $response = [
                'message' => count($invitations) . ' Invitation(s) Found',
                'status' => 1,
                'invitations' => $invitations
            ];
        } else {
            $response = [
                'message' => 'No Invitations Found',
                'status' => 0,
            ];
        }

        return response()->json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        return response()->json([
            'message' => 'Method not supported',
            'status' => 0,
        ], 405);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            //            'occasion' => 'required',
//            'room' => 'required',
            'date' => 'required',
            'time' => 'required',
            'location_id' => 'nullable|exists:locations,id',

            //            'image' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors()], 422);
        }
        //        $room = json_decode($request->input('room'));
        $date = json_decode($request->input('date'));
        $time = json_decode($request->input('time'));
        // Check if the room is already booked for the given date and time with status 0
//        $existingInvitation = Invitation::where('room->text',$room->text)
//            ->where('date->text',$date->text)
//            ->where('time->text', $time->text)
//            ->where('status', 0)
//            ->first();
//        dd($existingInvitation);

        //        if ($existingInvitation) {
//            return response()->json([
//                'message' => 'The room is already reserved for the given date and time.',
//                'status' => 0
//            ], 409);
//        }

        DB::beginTransaction();
        try {

            $input = $request->except('image');
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads'), $imageName); // Move file to public/uploads
                $input['image'] = $imageName; // Store only the image name, not the full path
                $imageUrl = url('uploads/' . $imageName);
            }
            $invitation = new Invitation();

            $invitation->occasion = $request->input('occasion');
            $invitation->room = $request->input('room');
            $invitation->date = $request->input('date');
            $invitation->time = $request->input('time');
            if ($request->input('turning')) {
                $invitation->turning = $request->input('turning');
            }
            if ($request->input('occasion')) {
                $invitation->occasion = $request->input('occasion');
            }
            if ($request->input('room')) {
                $invitation->room = $request->input('room');
            }
            $invitation->host_name = $request->input('host_name');
            $invitation->host_contact = $request->input('host_contact');
            $invitation->honoree_name = $request->input('honoree_name');
            $invitation->user_id = $request->id;
            $invitation->template_id = $request->template_id;
            $invitation->location_id = $request->input('location_id');
            $invitation->image = $input['image'];

            $invitation->save();
            $storedFile = $invitation->image;
            $invitation->image = $storedFile ? url('uploads/' . $storedFile) : null;
            $invitation->location_details = Location::find($invitation->location_id);

            DB::commit();


            $response = [
                'message' => 'Invitation Added Successfully',
                'status' => 1,
                'data' => $invitation
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

        return response()->json($response, $response_code);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $invitation = Invitation::find($id);
        if ($invitation) {
            $response = [
                'message' => "Invitation Found Successfully",
                'status' => 1,
                'invitation_data' => $invitation
            ];
        } else {
            $response = [
                'message' => 'No Invitation is available against id = ' . $id,
                'status' => 0,
            ];
        }
        return response()->json($response, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        return response()->json([
            'message' => 'Method not supported',
            'status' => 0,
        ], 405);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $id = $request->id;

        if ($request->has('location_id') && !Location::where('id', $request->input('location_id'))->exists()) {
            return response()->json([
                'message' => 'Invalid location selected',
                'status' => 0,
            ], 422);
        }

        $data = Invitation::find($id);

        if (is_null($data)) {
            $response = [
                'message' => 'No Invitation Found',
                'status' => 0
            ];
            $response_code = 200;
        } else {
            DB::beginTransaction();
            try {
                //                dd($request->input('occasion'));
//                $room = json_decode($request->input('room'));
//                $date = json_decode($request->input('date'));
                $time = json_decode($request->input('time'));
                // Check if the room is already booked for the given date and time with status 0
//                $existingInvitation = Invitation::where('room->text',$room->text)
//                    ->where('date->text',$date->text)
//                    ->where('time->text', $time->text)
//                    ->where('status', 0)
//                    ->first();
//        dd($existingInvitation);

                //                if ($existingInvitation) {
//                    return response()->json([
//                        'message' => 'The room is already reserved for the given date and time.',
//                        'status' => 0
//                    ], 409); // Conflict status code
//                }
                // Update fields based on form-data
                $data->occasion = $request->input('occasion');
                //                $data->room = $request->input('room');
                $data->date = $request->input('date');
                $data->time = $request->input('time');
                if ($request->input('turning')) {
                    $data->turning = $request->input('turning');
                }
                $data->host_name = $request->input('host_name');
                $data->host_contact = $request->input('host_contact');
                $data->honoree_name = $request->input('honoree_name');
                if ($request->has('location_id')) {
                    $data->location_id = $request->input('location_id');
                }


                // Handle image upload if a new image is provided
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $image->move(public_path('uploads'), $imageName); // Move file to public/uploads
                    $data->image = $imageName; // Store only the image name, not the full path
                }

                $data->save();
                $data->image = url('uploads/', $data->image);
                $data->location_details = Location::find($data->location_id);
                // Prepare the response
                $response = [
                    'message' => 'Invitation Updated Successfully',
                    'status' => 1,
                    'data' => $data // Optionally, you can return the updated $data object
                ];
                $response_code = 200;

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $response = [
                    'message' => "Internal Server Error",
                    'status' => 0,
                    'error_message' => $e->getMessage()
                ];
                $response_code = 500;
            }
        }

        // Return the response as JSON
        return response()->json($response, $response_code);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $invitation = Invitation::find($id);
        if ($invitation) {
            DB::beginTransaction();
            try {
                $invitation->delete();
                DB::commit();
                $response = [
                    'message' => 'Invitation Delete Successfully',
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
                'message' => 'No Invitation Found',
                'status' => 0
            ];
            $response_code = 200;
        }
        return response()->json($response, $response_code);

    }
}
