<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TemplateRequest;
use App\Models\Invitation;
use App\Models\Template;
use Dotenv\Validator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Storage;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $template = Template::orderBy('id', 'desc')->get();

        return view('admin.templates.index', compact('template'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.templates.addTemplate');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TemplateRequest $request)
    {
        $user = Auth::user();
        try {
            $input = $request->except('image', 'image_2');

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads'), $imageName); // Move file to public/uploads
                $input['image'] = $imageName; // Store only the image name, not the full path
            }
            if($request->hasFile('image_2')){
                $image = $request->file('image_2');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads'), $imageName); // Move file to public/uploads
                $input['image_2'] = $imageName;
            }

            $input['user_id'] = $user->id; // Assuming you want to store the user_id of the authenticated user
            $template = Template::create($input);

            return redirect()->route('admin_template.index')->with([
                'job' => 'Added',
                'status' => 'Template Added Successfully',
                'icon' => 'success',
            ]);
        } catch (\Exception $e) {
            \Log::error('Template Addition Failed: ' . $e->getMessage());
            return redirect()->back()->with([
                'job' => 'Failed',
                'status' => $e->getMessage(),
                'icon' => 'warning',
            ]);
        }
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
        $data = Template::find($id);

        if (!$data) {
            return redirect()->route('admin_template.index')->with([
                'icon' => 'warning',
                'job' => 'Failed',
                'status' => 'Template not found',
            ]);
        }

        DB::beginTransaction();
        try {
            $input = $request->except('image','image_2');

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads'), $imageName); // Move file to public/uploads
                $input['image'] = $imageName; // Store only the image name, not the full path
            }
            if($request->hasFile('image_2')){
                $image = $request->file('image_2');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads'), $imageName); // Move file to public/uploads
                $input['image_2'] = $imageName;
            }


            $data->update($input);

            DB::commit();
            return redirect()->route('admin_template.index')->with([
                'icon' => 'success',
                'job' => 'Success',
                'status' => 'Template Updated Successfully',
            ]);
        } catch (Exception $th) {
            DB::rollBack();
            return redirect()->route('admin_template.index')->with([
                'icon' => 'error',
                'job' => 'Failed',
                'status' => 'Template Updating Failed: ' . $th->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $template = Template::find($id);
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

    public function edit($id)
    {

        $template = Template::find($id);
        return view("admin.templates.update", compact('template'));
    }
}
