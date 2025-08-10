<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consultant;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConsultantController extends Controller
{
    public function index(Request $request)
    {
        $query = Consultant::with('department');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $consultants = $query->orderBy('id', 'DESC')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $consultants,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $consultant = Consultant::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Consultant created successfully.',
            'data' => $consultant,
        ], 201);
    }

    public function show($id)
    {
        $consultant = Consultant::with('department')->find($id);

        if (!$consultant) {
            return response()->json([
                'success' => false,
                'message' => 'Consultant not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $consultant,
        ]);
    }

    public function update(Request $request, $id)
    {
        $consultant = Consultant::find($id);

        if (!$consultant) {
            return response()->json([
                'success' => false,
                'message' => 'Consultant not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'department_id' => 'sometimes|exists:departments,id',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($consultant->photo && Storage::disk('public')->exists($consultant->photo)) {
                Storage::disk('public')->delete($consultant->photo);
            }
            $validated['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $consultant->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Consultant updated successfully.',
            'data' => $consultant,
        ]);
    }

    public function destroy($id)
    {
        $consultant = Consultant::find($id);

        if (!$consultant) {
            return response()->json([
                'success' => false,
                'message' => 'Consultant not found.',
            ], 404);
        }

        // Optionally delete photo
        if ($consultant->photo && Storage::disk('public')->exists($consultant->photo)) {
            Storage::disk('public')->delete($consultant->photo);
        }

        $consultant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Consultant deleted successfully.',
        ]);
    }
}
