<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * Display a listing of the patients.
     */
    public function index(Request $request)
    {
        $query = Patient::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('mobile', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('profession')) {
            $query->where('profession', $request->profession);
        }

        $patients = $query->orderBy('id', 'DESC')->paginate(10)->onEachSide(1);
        $patients->appends($request->all());

        return response()->json([
            'success' => true,
            'data' => $patients
        ]);
    }

    /**
     * Store a newly created patient in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $patient = Patient::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Patient successfully created!',
            'data' => $patient
        ], 201);
    }

    /**
     * Display the specified patient.
     */
    public function show(string $id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $patient
        ]);
    }

    /**
     * Update the specified patient in storage.
     */
    public function update(Request $request, string $id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }

        $data = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $patient->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Patient successfully updated!',
            'data' => $patient
        ]);
    }

    /**
     * Remove the specified patient from storage.
     */
    public function destroy(string $id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }

        $patient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Patient successfully deleted!'
        ]);
    }
}
