<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MoneyReceipt;
use App\Models\MoneyReceiptDetail;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MoneyReceiptController extends Controller
{
    /**
     * Display a listing of the money receipts.
     */
    public function index(Request $request)
    {
        $query = MoneyReceipt::with(['patient']);

        if ($request->filled('search')) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $money_receipts = $query->orderBy('id', 'DESC')->paginate(10)->onEachSide(1);
        $money_receipts->appends($request->all());

        return response()->json([
            'success' => true,
            'data' => $money_receipts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $mr = new MoneyReceipt();
        $mr->patient_id = $request->patient_id;
        $mr->remark = $request->remark;
        $mr->receipt_total = $request->receipt_total;
        $mr->vat = $request->vat;
        $mr->discount = $request->discount;
        $mr->save();

        $items = $request->items;
        foreach ($items as $item) {
            $details = new MoneyReceiptDetail();
            $details->money_receipt_id = $mr->id;
            $details->service_id = $item['service_id'];
            $details->price = $item['price'];
            $details->qty = $item['quantity'];
            $details->vat = $item['vat'];
            $details->discount = $item['discount'];
            $details->save();
        }
        return response()->json($mr, 200);
    }

    /**
     * Display the specified money receipt.
     */
    public function show($id)
    {
        $moneyReceipt = MoneyReceipt::with(['patient', 'moneyReceiptDetails.service'])->find($id);

        if (!$moneyReceipt) {
            return response()->json([
                'success' => false,
                'message' => 'Money receipt not found',
            ], 404);
        }

        // Calculate totals
        $subtotal = 0;
        $details = $moneyReceipt->moneyReceiptDetails->map(function ($detail) use (&$subtotal) {
            $lineTotal = $detail->qty * $detail->price + $detail->vat - $detail->discount;
            $subtotal += $lineTotal;

            $detail->lineTotal = $lineTotal;

            return $detail;
        });

        $tax = $subtotal * 0.05; // Assuming tax is 5%

        return response()->json([
            'success' => true,
            'data' => [
                'moneyReceipt' => $moneyReceipt,
                'details' => $details,
                'subtotal' => $subtotal,
                'tax' => $tax,
            ],
        ]);
    }

    /**
     * Update the specified money receipt.
     */
    public function update(Request $request, $id)
    {
        $moneyReceipt = MoneyReceipt::find($id);

        if (!$moneyReceipt) {
            return response()->json([
                'success' => false,
                'message' => 'Money receipt not found',
            ], 404);
        }

        $request->validate([
            'patient_id' => 'sometimes|required|exists:patients,id',
        ]);

        $moneyReceipt->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Money receipt updated successfully!',
            'data' => $moneyReceipt,
        ]);
    }

    /**
     * Remove the specified money receipt.
     */
    public function destroy($id)
    {
        $moneyReceipt = MoneyReceipt::find($id);

        if (!$moneyReceipt) {
            return response()->json([
                'success' => false,
                'message' => 'Money receipt not found',
            ], 404);
        }

        // Delete money receipt details first
        $moneyReceipt->moneyReceiptDetails()->delete();
        $moneyReceipt->delete();

        return response()->json([
            'success' => true,
            'message' => 'Money receipt deleted successfully!',
        ]);
    }

    /**
     * Generate a new receipt number.
     */
    public function generateReceiptNumber()
    {
        $lastReceipt = MoneyReceipt::max('id');
        $newReceiptNo = "MR-" . str_pad($lastReceipt + 1, 5, '0', STR_PAD_LEFT);

        return response()->json([
            'success' => true,
            'new_receipt_no' => $newReceiptNo,
        ]);
    }

    /**
     * Get a list of services for the money receipt.
     */
    public function getServices()
    {
        $services = Service::select('id', 'name', 'price')->get();

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }
}
