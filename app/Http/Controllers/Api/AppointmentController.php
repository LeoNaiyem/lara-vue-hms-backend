<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Appointment::with('patient', 'doctor');

        if ($request->filled('search')) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $appointments = $query->orderBy('id', 'DESC')->paginate(10)->onEachSide(1);
        $appointments->appends($request->all());

        return response()->json([
            'success' => true,
            'data' => $appointments,
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

        $appointment = Appointment::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!',
            'data' => $appointment,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $appointment = Appointment::with('patient', 'doctor')->find($id);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $appointment,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found',
            ], 404);
        }

        $data = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $appointment->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!',
            'data' => $appointment,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found',
            ], 404);
        }

        $appointment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!',
        ]);
    }
}
