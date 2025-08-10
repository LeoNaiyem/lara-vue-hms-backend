<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Designation;
use Illuminate\Support\Facades\Storage;

class DesignationController extends Controller
{
    public function index(Request $request)
    {
        $query = Designation::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $designations = $query->orderBy('id', 'DESC')->paginate(10)->onEachSide(1);
        $designations->appends($request->all());

        return response()->json([
            'success' => true,
            'data' => $designations,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $designation = Designation::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!',
            'data' => $designation,
        ], 201);
    }

    public function show($id)
    {
        $designation = Designation::find($id);

        if (!$designation) {
            return response()->json([
                'success' => false,
                'message' => 'Designation not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $designation,
        ]);
    }

    public function update(Request $request, $id)
    {
        $designation = Designation::find($id);

        if (!$designation) {
            return response()->json([
                'success' => false,
                'message' => 'Designation not found',
            ], 404);
        }

        $data = $request->all();

        if ($request->hasFile('photo')) {
            if ($designation->photo) {
                Storage::disk('public')->delete($designation->photo);
            }
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $designation->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!',
            'data' => $designation,
        ]);
    }

    public function destroy($id)
    {
        $designation = Designation::find($id);

        if (!$designation) {
            return response()->json([
                'success' => false,
                'message' => 'Designation not found',
            ], 404);
        }

        if ($designation->photo) {
            Storage::disk('public')->delete($designation->photo);
        }

        $designation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!',
        ]);
    }
}
