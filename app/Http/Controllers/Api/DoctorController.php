<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;
use App\Models\Designation;
use App\Models\Department;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Doctor::with(['department', 'designation']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $doctors = $query->orderBy('id', 'DESC')->paginate(10)->onEachSide(1);
        $doctors->appends($request->all());

        return response()->json([
            'success' => true,
            'data' => $doctors
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $doctor = Doctor::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!',
            'data' => $doctor
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $doctor = Doctor::with(['department', 'designation'])->find($id);

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $doctor
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $doctor = Doctor::find($id);

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found'
            ], 404);
        }

        $data = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $doctor->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!',
            'data' => $doctor
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $doctor = Doctor::find($id);

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found'
            ], 404);
        }

        $doctor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!'
        ]);
    }
}
