<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Vahicle;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vehicle = Vahicle::with('category');
        if ($request->has('search')) {
            $vehicle = $vehicle->where('name', 'LIKE', '%' . $request->search . '%')->orWhere('number_plate', 'LIKE', '%' . $request->search . '%');
        }
        $vehicle = $vehicle->latest('id')->paginate($request->per_page ?? 10);
        return response()->json([
            'status'  => true,
            'message' => 'Vehicle retreived successfully',
            'data'    => $vehicle,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id'  => 'required|numeric',
            'name'         => 'required|string|max:255',
            'number_plate' => 'required|string|max:40',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors(),
            ]);
        }
        $vehicle = Vahicle::create([
            'category_id'  => $request->category_id,
            'name'         => $request->name,
            'number_plate' => $request->number_plate,
        ]);
        return response()->json([
            'status'  => true,
            'message' => 'Vehicle added successfully',
            'data'    => $vehicle,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'category_id'  => 'required|numeric',
            'name'         => 'required|string|max:255',
            'number_plate' => 'required|string|max:40',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors(),
            ]);
        }
        try {
            $vehicle = Vahicle::findOrFail($id);
            $vehicle->update([
                'category_id'  => $request->category_id,
                'name'         => $request->name,
                'number_plate' => $request->number_plate,
            ]);
            return response()->json([
                'status'  => true,
                'message' => 'Vehicle updated successfully',
                'data'    => $vehicle,
            ]);
        } catch (Exception $e) {
            Log::info("Vehicle update error: " . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'No data found.',
                'data'    => null,
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $vehicle = Vahicle::findOrFail($id);
            $vehicle->delete();
            return response()->json([
                'status'  => true,
                'message' => 'Vehicle deleted successfully.',
                'data'    => $vehicle,
            ]);
        } catch (Exception $e) {
            Log::info("Vehicle delete error: " . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'No data found.',
                'data'    => null,
            ]);
        }
    }
}
