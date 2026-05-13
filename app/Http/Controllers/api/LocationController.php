<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Location::query()->orderBy('id', 'DESC');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $locations = $query->get();

        return response()->json([
            'message' => count($locations) . ' Location(s) Found',
            'status' => 1,
            'data' => $locations,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'phone' => 'required|string|max:30',
            'email' => 'required|email|max:100',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        // DB columns are non-nullable; treat omitted/empty optional fields as empty string.
        $data['address_line_1'] = $data['address_line_1'] ?? '';
        $data['city'] = $data['city'] ?? '';
        $data['slug'] = Str::slug($data['name']) . '-' . time();

        if (!isset($data['country'])) {
            $data['country'] = 'Pakistan';
        }

        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }

        $location = Location::create($data);

        return response()->json([
            'message' => 'Location added successfully',
            'status' => 1,
            'data' => $location,
        ], 201);
    }

    public function show($id)
    {
        $location = Location::find($id);

        if (!$location) {
            return response()->json([
                'message' => 'Location not found',
                'status' => 0,
            ], 404);
        }

        return response()->json([
            'message' => 'Location found',
            'status' => 1,
            'data' => $location,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $location = Location::find($id);

        if (!$location) {
            return response()->json([
                'message' => 'Location not found',
                'status' => 0,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100',
            'address_line_1' => 'sometimes|required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'sometimes|required|string|max:100',
            'state' => 'sometimes|required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'phone' => 'sometimes|required|string|max:30',
            'email' => 'sometimes|required|email|max:100',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        foreach (['address_line_1', 'city'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] === null) {
                $data[$field] = '';
            }
        }

        if (isset($data['name']) && $data['name'] !== $location->name) {
            $data['slug'] = Str::slug($data['name']) . '-' . time();
        }

        $location->update($data);

        return response()->json([
            'message' => 'Location updated successfully',
            'status' => 1,
            'data' => $location,
        ], 200);
    }

    public function destroy($id)
    {
        $location = Location::find($id);

        if (!$location) {
            return response()->json([
                'message' => 'Location not found',
                'status' => 0,
            ], 404);
        }

        $location->delete();

        return response()->json([
            'message' => 'Location deleted successfully',
            'status' => 1,
        ], 200);
    }
}
