<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicineType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MedicineTypeController extends Controller
{
    /**
     * Display a listing of the medicine types.
     */
    public function index(Request $request)
    {
        $query = MedicineType::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $medicineTypes = $query->orderBy('id', 'DESC')->paginate(10)->onEachSide(1);
        $medicineTypes->appends($request->all());

        return response()->json([
            'success' => true,
            'data' => $medicineTypes,
        ]);
    }

    /**
     * Store a newly created medicine type.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $medicineType = MedicineType::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!',
            'data' => $medicineType,
        ], 201);
    }

    /**
     * Display the specified medicine type.
     */
    public function show($id)
    {
        $medicineType = MedicineType::find($id);

        if (!$medicineType) {
            return response()->json([
                'success' => false,
                'message' => 'Medicine type not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $medicineType,
        ]);
    }

    /**
     * Update the specified medicine type.
     */
    public function update(Request $request, $id)
    {
        $medicineType = MedicineType::find($id);

        if (!$medicineType) {
            return response()->json([
                'success' => false,
                'message' => 'Medicine type not found',
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('photo')) {
            if ($medicineType->photo) {
                Storage::disk('public')->delete($medicineType->photo);
            }
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $medicineType->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!',
            'data' => $medicineType,
        ]);
    }

    /**
     * Remove the specified medicine type.
     */
    public function destroy($id)
    {
        $medicineType = MedicineType::find($id);

        if (!$medicineType) {
            return response()->json([
                'success' => false,
                'message' => 'Medicine type not found',
            ], 404);
        }

        if ($medicineType->photo) {
            Storage::disk('public')->delete($medicineType->photo);
        }

        $medicineType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!',
        ]);
    }
}
