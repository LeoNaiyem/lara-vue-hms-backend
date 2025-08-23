<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admission;
use App\Models\Patient;
use App\Models\Bed;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class AdmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Admission::with(['patient', 'department', 'ref_doctor', 'under_doctor', 'bed']);

        if ($request->filled('search')) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $admissions = $query->orderBy('id', 'DESC')->paginate(10)->onEachSide(1);
        $admissions->appends($request->all());

        return response()->json([
            'success' => true,
            'data' => $admissions,
        ]);
    }

    public function getAvailableBeds($type)
    {
        $beds = Bed::where('bed_type', $type)
            ->where('status', 'Available')
            ->select('id', 'bed_number')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $beds,
        ]);
    }

    public function allAvailableBeds()
    {
        $beds = Bed::where('status', 'Available')->select('id', 'bed_number','bed_type')->get();
        return response()->json([
            'success' => true,
            'data' => $beds
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'advance' => $request->input('advance', 0),
        ]);

        $data = $request->validate([
            'patient_id' => 'required|integer|exists:patients,id',
            'ref_doctor_id' => 'required|integer|exists:doctors,id',
            'under_doctor_id' => 'required|integer|exists:doctors,id',
            'admission_date' => 'required|date',
            'bed_id' => 'required|integer|exists:beds,id',
            'department_id' => 'required|integer|exists:departments,id',
            'advance' => 'required|numeric',
            'remark' => 'nullable|string',
            'problem' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $bed = Bed::where('id', $data['bed_id'])
                ->where('status', 'Available')
                ->lockForUpdate()
                ->first();

            if (!$bed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected bed is no longer available.',
                ], 422);
            }

            $admission = Admission::create($data);

            $bed->status = 'Occupied';
            $bed->save();

            $invoice = new Invoice();
            $invoice->patient_id = $data['patient_id'];
            $invoice->remark = $data['remark'] ?? null;
            $invoice->invoice_total = 0;
            $invoice->discount = 0;
            $invoice->vat = 0;
            $invoice->paid_total = $data['advance'];
            $invoice->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Admission successfully created.',
                'data' => $admission,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create admission.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $admission = Admission::with(['patient', 'department', 'ref_doctor', 'under_doctor', 'bed'])->find($id);

        if (!$admission) {
            return response()->json([
                'success' => false,
                'message' => 'Admission not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $admission,
        ]);
    }

    public function update(Request $request, $id)
    {
        $admission = Admission::find($id);

        if (!$admission) {
            return response()->json([
                'success' => false,
                'message' => 'Admission not found.',
            ], 404);
        }

        $data = $request->validate([
            'patient_id' => 'sometimes|integer|exists:patients,id',
            'ref_doctor_id' => 'sometimes|integer|exists:doctors,id',
            'under_doctor_id' => 'sometimes|integer|exists:doctors,id',
            'admission_date' => 'sometimes|date',
            'bed_id' => 'sometimes|integer|exists:beds,id',
            'department_id' => 'sometimes|integer|exists:departments,id',
            'advance' => 'sometimes|numeric',
            'remark' => 'nullable|string',
            'problem' => 'nullable|string'
        ]);

        $admission->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Admission successfully updated.',
            'data' => $admission,
        ]);
    }

    public function destroy($id)
    {
        $admission = Admission::find($id);

        if (!$admission) {
            return response()->json([
                'success' => false,
                'message' => 'Admission not found.',
            ], 404);
        }

        $admission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Admission successfully deleted.',
        ]);
    }
}
