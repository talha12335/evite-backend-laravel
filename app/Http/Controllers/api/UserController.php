<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = User::all();
        if ($user) {
            $response = [
                'message' => count($user) . ' Users Found',
                'status' => 1,
                'user' => $user
            ];
        } else {
            $response = [
                'message' => count($user) . ' Users Found',
                'status' => 0,
            ];
        }

        return response()->json($response, 200);
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
            'email' => 'required|string|email',
        ]);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        } else {
            $user = User::where('email', $request->email)->exists();
            if ($user) {
                $user = User::where('email', $request->email)->first();
                $response = [
                    'message' => 'User Already Exist',
                    'status' => 1,
                    'data' => $user
                ];
                $response_code = 200;
            } else {


                DB::beginTransaction();
                try {
                    $data = $request->all();
                    $data['role_id'] = 4;
                    $user = User::create($data);
                    DB::commit();
                    $response = [
                        'message' => 'User Added Successfully',
                        'status' => 1,
                        'data' => $user
                    ];
                    $response_code = 200;
                } catch (\Exception $e) {
                    $response_code = 500;
                    $response = $e->getMessage();
                }
            }
        }
        return response()->json($response, $response_code);
    }
}
