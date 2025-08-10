<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MedicineController extends Controller
{
    /**
     * Display a listing of the medicines.
     */
    public function index(Request $request)
    {
        $query = Medicine::with(['medicineCategory', 'medicineType']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('medicine_category_id')) {
            $query->where('medicine_category_id', $request->medicine_category_id);
        }

        if ($request->filled('medicine_type_id')) {
            $query->where('medicine_type_id', $request->medicine_type_id);
        }

        $medicines = $query->orderBy('id', 'DESC')->paginate(10)->onEachSide(1);
        $medicines->appends($request->all());

        return response()->json([
            'success' => true,
            'data' => $medicines,
        ]);
    }

    /**
     * Store a newly created medicine.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'medicine_category_id' => 'required|exists:medicine_categories,id',
            'medicine_type_id' => 'required|exists:medicine_types,id',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $medicine = Medicine::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!',
            'data' => $medicine,
        ], 201);
    }

    /**
     * Display the specified medicine.
     */
    public function show($id)
    {
        $medicine = Medicine::with('medicineCategory', 'medicineType')->find($id);

        if (!$medicine) {
            return response()->json([
                'success' => false,
                'message' => 'Medicine not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $medicine,
        ]);
    }

    /**
     * Update the specified medicine.
     */
    public function update(Request $request, $id)
    {
        $medicine = Medicine::find($id);

        if (!$medicine) {
            return response()->json([
                'success' => false,
                'message' => 'Medicine not found',
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'medicine_category_id' => 'sometimes|required|exists:medicine_categories,id',
            'medicine_type_id' => 'sometimes|required|exists:medicine_types,id',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('photo')) {
            if ($medicine->photo) {
                Storage::disk('public')->delete($medicine->photo);
            }
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $medicine->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!',
            'data' => $medicine,
        ]);
    }

    /**
     * Remove the specified medicine.
     */
    public function destroy($id)
    {
        $medicine = Medicine::find($id);

        if (!$medicine) {
            return response()->json([
                'success' => false,
                'message' => 'Medicine not found',
            ], 404);
        }

        if ($medicine->photo) {
            Storage::disk('public')->delete($medicine->photo);
        }

        $medicine->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!',
        ]);
    }
}
