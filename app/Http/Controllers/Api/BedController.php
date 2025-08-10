<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BedController extends Controller
{
    /**
     * Display a listing of the beds.
     */
    public function index(Request $request)
    {
        $query = Bed::with(['ward']);

        if ($request->filled('search')) {
            $query->where('bed_number', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $beds = $query->orderBy('id', 'DESC')->paginate(10)->onEachSide(1);
        $beds->appends($request->all());

        return response()->json([
            'success' => true,
            'data' => $beds,
        ]);
    }

    /**
     * Store a newly created bed.
     */
    public function store(Request $request)
    {
        $data = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $bed = Bed::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!',
            'data' => $bed,
        ], 201);
    }

    /**
     * Display the specified bed.
     */
    public function show($id)
    {
        $bed = Bed::find($id);

        if (!$bed) {
            return response()->json([
                'success' => false,
                'message' => 'Bed not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $bed,
        ]);
    }

    /**
     * Update the specified bed.
     */
    public function update(Request $request, $id)
    {
        $bed = Bed::find($id);

        if (!$bed) {
            return response()->json([
                'success' => false,
                'message' => 'Bed not found',
            ], 404);
        }

        $data = $request->all();

        if ($request->hasFile('photo')) {
            if ($bed->photo) {
                Storage::disk('public')->delete($bed->photo);
            }
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $bed->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!',
            'data' => $bed,
        ]);
    }

    /**
     * Remove the specified bed.
     */
    public function destroy($id)
    {
        $bed = Bed::find($id);

        if (!$bed) {
            return response()->json([
                'success' => false,
                'message' => 'Bed not found',
            ], 404);
        }

        if ($bed->photo) {
            Storage::disk('public')->delete($bed->photo);
        }

        $bed->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!',
        ]);
    }
}
