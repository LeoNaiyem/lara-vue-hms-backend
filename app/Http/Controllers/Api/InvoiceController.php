<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Bed;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the invoices.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['patient']);

        if ($request->filled('search')) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $invoices = $query->orderBy('id', 'DESC')->paginate(10)->onEachSide(1);
        $invoices->appends($request->all());

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $invoices = new Invoice();
        $invoices->patient_id = $request->patient_id;
        $invoices->invoice_total = $request->invoice_total;
        $invoices->paid_total = $request->paid_total;
        $invoices->discount = 0;
        $invoices->vat = 0;
        $invoices->payment_term = $request->payment_term;
        $invoices->remark = $request->remark;
        $invoices->created_at = now()->format('F d, Y');
        $invoices->save();
        foreach ($request->services as $service) {
            $details = new InvoiceDetail();
            $details->invoice_id = $invoices->id;
            $details->service_id = $service['id'];
            $details->unit = $service['unit'];
            $details->price = $service['price'];
            $details->discount = $service['discount'];
            $details->vat = $service['vat'];
            $details->save();
        }

        //change the bed status
        // $bedId = Admission::where('patient_id', $invoices->patient_id)
        //     ->value('bed_id');   // This directly returns the scalar bed_id

        $bed = null;
        $admission = Admission::where('patient_id', $invoices->patient_id)->first();
        if ($admission && $admission->bed_id) {
            $bed = Bed::find($admission->bed_id);
            if ($bed) {
                $bed->status = "Available";
                $bed->save();
            }
        }


        return response()->json(['created invoice' => $invoices, 'occupied bed' => $bed]);
    }

    /**
     * Display the specified resource.
     */
    public function show($invoiceId)
    {
        $invoice = DB::table('invoices as i')
            ->join('invoice_details as d', 'i.id', '=', 'd.invoice_id')
            ->join('services as s', 's.id', '=', 'd.service_id')
            ->where('i.id', $invoiceId)
            ->get([
                'i.patient_id',
                'i.invoice_total',
                'i.paid_total',
                'i.payment_term',
                's.name',
                'd.price',
                'd.unit',
                'd.discount',
                'd.vat'
            ]);

        if ($invoice->isEmpty()) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $patient = Patient::find($invoice[0]->patient_id);
        // $details = InvoiceDetail::where('invoice_id', $invoiceId)->get();

        return response()->json([
            'invoice' => $invoice,
            // 'details' => $details,
            'patient' => $patient
        ]);
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, $id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        $request->validate([
            'patient_id' => 'sometimes|required|exists:patients,id',
        ]);

        $invoiceData = $request->all();
        $invoice->update($invoiceData);

        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully!',
            'data' => $invoice,
        ]);
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy($id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        // Delete invoice details first (cascade delete)
        $invoice->invoiceDetails()->delete();
        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invoice deleted successfully!',
        ]);
    }

    /**
     * Create a new bill (separate from API resource methods).
     */
    public function createBill(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'service_id' => 'required|exists:services,id',
            'unit' => 'required|integer',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'vat' => 'nullable|numeric',
        ]);

        $details = new InvoiceDetail();
        $details->invoice_id = $request->invoice_id;
        $details->service_id = $request->service_id;
        $details->unit = $request->unit;
        $details->price = $request->price;
        $details->discount = $request->discount;
        $details->vat = $request->vat;
        $details->save();

        return response()->json([
            'success' => true,
            'message' => 'Invoice details added successfully!',
        ]);
    }

    /**
     * Get patients with their latest invoice for billing purposes.
     */
    public function bill(Request $request)
    {
        $patients = Patient::with('latestInvoice')->get();
        $services = Service::select('id', 'name', 'price')->get();

        return response()->json([
            'success' => true,
            'patients' => $patients,
            'services' => $services,
        ]);
    }
}
