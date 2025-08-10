<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\MedicineCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    /**
     * Display a listing of the services.
     */
    public function index(Request $request)
    {
        $query = Service::with('medicineCategory');

        // Optional search by service name or description
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('medicine_category_id', 'like', '%' . $request->search . '%');
        }

        // Optional filter by medicine_category_id
        if ($request->filled('medicine_category_id')) {
            $query->where('medicine_category_id', $request->medicine_category_id);
        }

        $services = $query->orderBy('id', 'DESC')->paginate(10)->onEachSide(1);
        $services->appends($request->all());

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    /**
     * Store a newly created service.
     */
    public function store(Request $request)
    {
        $data = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $service = Service::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!',
            'data' => $service,
        ], 201);
    }

    /**
     * Display the specified service.
     */
    public function show($id)
    {
        $service = Service::with('medicineCategory')->find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $service,
        ]);
    }

    /**
     * Update the specified service.
     */
    public function update(Request $request, $id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found',
            ], 404);
        }

        $data = $request->all();

        if ($request->hasFile('photo')) {
            if ($service->photo) {
                Storage::disk('public')->delete($service->photo);
            }
            $data['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $service->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!',
            'data' => $service,
        ]);
    }

    /**
     * Remove the specified service.
     */
    public function destroy($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found',
            ], 404);
        }

        if ($service->photo) {
            Storage::disk('public')->delete($service->photo);
        }

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!',
        ]);
    }
}
