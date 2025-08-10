<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ward;
use Illuminate\Http\Request;

class WardController extends Controller
{
    /**
     * Display a listing of wards.
     */
    public function index(Request $request)
    {
        $query = Ward::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('ward_code', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->is_active);
        }

        $wards = $query->orderBy('id', 'DESC')->paginate(10)->onEachSide(1);
        $wards->appends($request->all());

        return response()->json([
            'success' => true,
            'data' => $wards,
        ]);
    }

    /**
     * Store a newly created ward.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'ward_code' => 'required|string|max:20|unique:wards,ward_code',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->all();
        $ward = Ward::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!',
            'data' => $ward,
        ], 201);
    }

    /**
     * Display the specified ward.
     */
    public function show($id)
    {
        $ward = Ward::find($id);

        if (!$ward) {
            return response()->json([
                'success' => false,
                'message' => 'Ward not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ward,
        ]);
    }

    /**
     * Update the specified ward.
     */
    public function update(Request $request, $id)
    {
        $ward = Ward::find($id);

        if (!$ward) {
            return response()->json([
                'success' => false,
                'message' => 'Ward not found',
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'ward_code' => 'sometimes|required|string|max:20|unique:wards,ward_code,' . $ward->id,
            'capacity' => 'sometimes|required|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $ward->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!',
            'data' => $ward,
        ]);
    }

    /**
     * Remove the specified ward.
     */
    public function destroy($id)
    {
        $ward = Ward::find($id);

        if (!$ward) {
            return response()->json([
                'success' => false,
                'message' => 'Ward not found',
            ], 404);
        }

        $ward->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!',
        ]);
    }
}
