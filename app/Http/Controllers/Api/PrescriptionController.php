<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Prescription;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Consultant;
use App\Models\PrescriptionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PrescriptionController extends Controller
{
    /**
     * Display a listing of the prescriptions.
     */
    public function index(Request $request)
    {
        $query = Prescription::with(['patient', 'consultant']);

        if ($request->filled('search')) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('consultant_id')) {
            $query->where('consultant_id', $request->consultant_id);
        }

        $prescriptions = $query->orderBy('id', 'DESC')->paginate(10)->onEachSide(1);
        $prescriptions->appends($request->all());

        return response()->json([
            'success' => true,
            'data' => $prescriptions,
        ]);
    }

    /**
     * Store a newly created prescription.
     */
    public function store(Request $request)
    {
        $ps = new Prescription();
        $ps->patient_id = $request->patient_id;
        $ps->consultant_id = $request->consultant_id;
        $ps->cc = $request->cc;
        $ps->rf = $request->rf;
        $ps->investigation = $request->investigation;
        $ps->advice = $request->advice;
        $ps->save();

        $items = $request->rx;
        foreach ($items as $item) {
            $psd = new PrescriptionDetail();
            $psd->prescription_id = $ps->id;
            $psd->medicine_id = $item['medicine_id'];
            $psd->dose = $item['dose'];
            $psd->days = $item['days'];
            $psd->suggestion = $item['suggestion'];
            $psd->medicine_name = $item['medicine_name'];
            $psd->save();
        }
        return response()->json($ps);
    }

    /**
     * Display the specified prescription.
     */
    public function show(string $id)
    {
        $prescription = DB::table('prescriptions as p')
            ->join('prescription_details as d', 'p.id', '=', 'd.prescription_id')
            ->join('medicines as m', 'm.id', '=', 'd.medicine_id')
            ->where('p.id', $id)
            ->get([
                'p.patient_id',
                'p.consultant_id',
                'p.cc',
                'p.rf',
                'p.investigation',
                'p.advice',
                'm.name as medicine_name',
                'd.dose',
                'd.days',
                'd.suggestion',
            ]);

        if ($prescription->isEmpty()) {
            return response()->json(['message' => 'prescription not found'], 404);
        }

        $patient = Patient::find($prescription[0]->patient_id);
        // $details = InvoiceDetail::where('invoice_id', $invoiceId)->get();

        return response()->json([
            'prescription' => $prescription,
            // 'details' => $details,
            'patient' => $patient
        ]);
    }

    /**
     * Update the specified prescription.
     */
    public function update(Request $request, $id)
    {
        $prescription = Prescription::find($id);

        if (!$prescription) {
            return response()->json([
                'success' => false,
                'message' => 'Prescription not found',
            ], 404);
        }

        $request->validate([
            'patient_id' => 'sometimes|required|exists:patients,id',
            'consultant_id' => 'sometimes|required|exists:consultants,id',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('photo')) {
            if ($prescription->photo) {
                Storage::disk('public')->delete($prescription->photo);
            }
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $prescription->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!',
            'data' => $prescription,
        ]);
    }

    /**
     * Remove the specified prescription.
     */
    public function destroy($id)
    {
        $prescription = Prescription::find($id);

        if (!$prescription) {
            return response()->json([
                'success' => false,
                'message' => 'Prescription not found',
            ], 404);
        }

        if ($prescription->photo) {
            Storage::disk('public')->delete($prescription->photo);
        }

        $prescription->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!',
        ]);
    }
}
