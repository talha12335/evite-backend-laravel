<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Template;
use App\Models\User;
use App\Models\UserTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $template = UserTemplate::all();
        if ($template) {
            $response = [
                'message' => count($template) . ' Template Found',
                'data' => $template,
                'status' => 1
            ];
        } else {
            $response = [
                'message' => count($template) . ' Template Found',
                'status' => 0
            ];
        }
        return response()->json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function admin_template(){
        $template = Template::all();
        if ($template) {
            foreach ($template as $templates) {
                $imageUrl = url('uploads/' . $templates->image);
                $templates->image_url = $imageUrl; // Add a new attribute for the full image URL
                $imagePath = 'uploads/' . $templates->image;

                $imageUrl = url('uploads/' . $templates->image_2);
                $templates->image_url_2 = $imageUrl; // Add a new attribute for the full image URL

// Check if file exists
                if (file_exists($imagePath)) {
                    $imageData = file_get_contents($imagePath);

                    // Get the mime type of the image
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->file($imagePath);

                    // Encode the image to base64
                    $base64 = base64_encode($imageData);

                    // Create the base64 image URL
                    $templates->image_baseUrl = 'data:' . $mimeType . ';base64,' . $base64;
                } else {
                    echo "File does not exist.";
                }            }

            $response = [
                'message' => count($template) . ' Template Found',
                'data' => $template,
                'status' => 1
            ];
        } else {
            $response = [
                'message' => count($template) . ' Template Found',
                'status' => 0
            ];
        }
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
{
    $existing_templates = UserTemplate::where('user_id', $request->id)->get();

    if ($existing_templates->isNotEmpty()) {
        // Iterate through each template and update the image URL
        foreach ($existing_templates as $template) {
            $imageUrl = url('uploads/' . $template->image);
            $template->image_url = $imageUrl; // Add a new attribute for the full image URL
        }

        $response = [
            'message' => 'Template Already Exist',
            'status' => 1,
            'data' => $existing_templates
        ];
        $response_code = 200;
    } else {
        return $this->create_new_template($request);
    }

    return response()->json($response, $response_code);
}

    public function create_new_template(Request $request)
    {
        // Validate the incoming request
        $validate = Validator::make($request->all(), [
            'image' => 'file|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'text1_color' => 'required|string|max:7',
            'text2_color' => 'required|string|max:7',
            'text3_color' => 'required|string|max:7',
            'id' => 'required|exists:users,id'
        ]);

        // Handle validation failure
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->messages()], 422);
        }

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
            $input['user_id'] = $request->id;

            $template = UserTemplate::create($input);
            $invitation = Invitation::where('user_id', $request->id)->orderBy('id','desc')->first();

            DB::commit();
            $template->image = $imageUrl;
            $response = [
                'message' => 'Template Added Successfully',
                'status' => 1,
                'data' => $template,
                'Invitaion_data' => $invitation
            ];
            $response_code = 200;
        } catch (\Exception $e) {
            DB::rollBack();
            $response_code = 500;
            $response = [
                'message' => 'An error occurred while adding the template',
                'status' => 0,
                'error' => $e->getMessage()
            ];
        }

        return response()->json($response, $response_code);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $template = UserTemplate::find($id);
        if (!$template) {
            $response = [
                'message' => 'No Template Found with id=' . $id,
                'status' => 0
            ];
        } else {
            $response = [
                'message' => 'Template Found Successfully',
                'status' => 1,
                'data' => $template
            ];
        }
        return response()->json($response, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = UserTemplate::find($id);
        if (is_null($data)) {
            $response = [
                'message' => 'No Template Found',
                'status' => 0
            ];
            $response_code = 200;
        } else {
            DB::beginTransaction();
            try {
                $data->image = is_null($request->image) ? $data->image : $request->image;
                $data->text1_color = is_null($request->text1_color) ? $data->text1_color : $request->text1_color;
                $data->text2_color = is_null($request->text2_color) ? $data->text2_color : $request->text2_color;
                $data->text3_color = is_null($request->text3_color) ? $data->text3_color : $request->text3_color;
                $data->save();
                $response = [
                    'message' => 'Template Updated Successfully',
                    'status' => 1,
                    'data' => $data
                ];
                $response_code = 200;
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                $response = [
                    'message' => "Intervel Server Error",
                    'status' => 0
                ];
                $response_code = 500;
            }
        }
        return response()->json($response, $response_code);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $template = UserTemplate::find($id);
        if ($template) {
            DB::beginTransaction();
            try {
                $template->delete();
                DB::commit();
                $response = [
                    'message' => 'Template Delete Successfully',
                    'status' => 1
                ];
                $response_code = 200;
            } catch (\Exception $e) {
                DB::rollBack();
                $response = [
                    'message' => 'Internal Server Error',
                    'status' => 0
                ];
                $response_code = 500;
            }
        } else {
            $response = [
                'message' => 'No Template Found',
                'status' => 0
            ];
            $response_code = 200;
        }
        return response()->json($response, $response_code);
    }
}
