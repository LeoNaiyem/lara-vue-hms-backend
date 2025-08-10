<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Support\Facades\Storage;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $departments = $query->orderBy('id', 'DESC')->paginate(10)->onEachSide(1);
        $departments->appends($request->all());

        return response()->json([
            'success' => true,
            'data' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $department = Department::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!',
            'data' => $department,
        ], 201);
    }

    public function show($id)
    {
        $department = Department::find($id);

        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'Department not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $department,
        ]);
    }

    public function update(Request $request, $id)
    {
        $department = Department::find($id);

        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'Department not found',
            ], 404);
        }

        $data = $request->all();

        if ($request->hasFile('photo')) {
            if ($department->photo) {
                Storage::disk('public')->delete($department->photo);
            }
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $department->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!',
            'data' => $department,
        ]);
    }

    public function destroy($id)
    {
        $department = Department::find($id);

        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'Department not found',
            ], 404);
        }

        if ($department->photo) {
            Storage::disk('public')->delete($department->photo);
        }

        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!',
        ]);
    }
}
