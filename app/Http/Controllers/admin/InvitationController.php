<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class InvitationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $invitation = Invitation::orderBy('id','desc')->get();
        
        return view('admin.invitation.index',compact('invitation'));
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'occasion' => 'required' ,
            'room' => 'required' ,
            'date' => 'required',
            'time' => 'required',
        ]);
        if($validate->fails()){
            return response()->json($validate->messages(), 200);
        }
        else{
            DB::beginTransaction();
            try{
                $invitation = new Invitation();

                $invitation->occasion = $request->input('occasion');
                $invitation->room = $request->input('room');
                $invitation->date = $request->input('date');
                $invitation->time = $request->input('time');
                $invitation->user_id = $request->id;

                $invitation->save();

                DB::commit();
                $response = [
                    'message' => 'Invitation Added Successfullt',
                    'status' => 1,
                    'data' => $invitation
                ];
                $response_code = 200;

            }
            catch(\Exception $e){
                $response = [
                    'message' => 'Internal Server Error',
                    'status' => 0,
                    'error_message' => $e->getMessage()
                ];
                $response_code = 500;
            }
        }
        return response()->json($response,$response_code);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invitation = Invitation::find($id);
        if($invitation){
            $response = [
                'message' => "Invitation Found Successfully",
                'status' => 1,
                'invitation_data' => $invitation
            ];
        }
        else{
            $response = [
                'message' => 'No Invitation is available against id = '.$id,
                'status' => 0,
            ];
        }
        return response()->json($response,200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        $data = Invitation::find($id);
        if(is_null($data)){
            $response = [
                'message' => 'No Invitation Found',
                'status' => 0
            ];
            $response_code = 200;

        }
        else{
            DB::beginTransaction();
            try {
                $data->occasion = is_null($request->occasion) ? $data->occasion : $request->occasion;
                $data->room = is_null($request->room) ? $data->room : $request->room;
                $data->date = is_null($request->date) ? $data->date : $request->date;
                $data->time = is_null($request->time) ? $data->time : $request->time;
                $data->save();
                $response = [
                    'message' => 'Invitation Updated Successfully',
                    'status' => 1,
                    'data' => $data
                ];
                $response_code = 200;
            } catch (\Exception $e) {
                //throw $th;
                DB::rollBack();
                $response = [
                    'message' => "Intervel Server Error",
                    'status' => 0,
                    'error_message' => $e->getMessage()
                ];
                $response_code = 500;
            }

        }
        return response()->json($response,$response_code);
    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
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
