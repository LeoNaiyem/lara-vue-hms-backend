<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicineCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MedicineCategoryController extends Controller
{
    /**
     * Display a listing of medicine categories.
     */
    public function index(Request $request)
    {
        $query = MedicineCategory::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $medicineCategories = $query->orderBy('id', 'DESC')->paginate(10)->onEachSide(1);
        $medicineCategories->appends($request->all());

        return response()->json([
            'success' => true,
            'data' => $medicineCategories,
        ]);
    }

    /**
     * Store a newly created medicine category.
     */
    public function store(Request $request)
    {
        $data = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $medicineCategory = MedicineCategory::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!',
            'data' => $medicineCategory,
        ], 201);
    }

    /**
     * Display the specified medicine category.
     */
    public function show($id)
    {
        $medicineCategory = MedicineCategory::find($id);

        if (!$medicineCategory) {
            return response()->json([
                'success' => false,
                'message' => 'Medicine category not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $medicineCategory,
        ]);
    }

    /**
     * Update the specified medicine category.
     */
    public function update(Request $request, $id)
    {
        $medicineCategory = MedicineCategory::find($id);

        if (!$medicineCategory) {
            return response()->json([
                'success' => false,
                'message' => 'Medicine category not found',
            ], 404);
        }

        $data = $request->all();

        if ($request->hasFile('photo')) {
            if ($medicineCategory->photo) {
                Storage::disk('public')->delete($medicineCategory->photo);
            }
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $medicineCategory->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!',
            'data' => $medicineCategory,
        ]);
    }

    /**
     * Remove the specified medicine category.
     */
    public function destroy($id)
    {
        $medicineCategory = MedicineCategory::find($id);

        if (!$medicineCategory) {
            return response()->json([
                'success' => false,
                'message' => 'Medicine category not found',
            ], 404);
        }

        if ($medicineCategory->photo) {
            Storage::disk('public')->delete($medicineCategory->photo);
        }

        $medicineCategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!',
        ]);
    }
}
