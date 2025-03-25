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
        $today        = now()->format('Y-m-d');
        $current_time = now()->format('H:i:s');
        $vehicleQuery = Vahicle::with('category', 'bookings');

        if ($request->has('search')) {
            $vehicleQuery->where(function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('number_plate', 'LIKE', '%' . $request->search . '%');
            });
        }

        $vehicles = $vehicleQuery->latest('id')->paginate($request->per_page ?? 100);

        $vehicles->getCollection()->transform(function ($vehicle) use ($today, $current_time) {
            $all_booking_dates = $vehicle->bookings->flatMap(function ($booking) {
                return $booking->booking_dates;
            })->unique()->toArray();

            $is_booked_today = in_array($today, $all_booking_dates);
            $is_booked       = false;

            foreach ($vehicle->bookings as $booking) {
                if ($booking->booking_type === 'multiple_day' && in_array($today, $booking->booking_dates)) {
                    $is_booked = true;
                    break;
                } elseif ($booking->booking_type === 'single_day' && $booking->booking_time_to >= $current_time) {
                    $is_booked = true;
                    break;
                }
            }

            return [
                "id"       => $vehicle->id,
                "title"    => $vehicle->name,
                "code"     => $vehicle->number_plate,
                'category' => $vehicle->category,
                'book'     => $is_booked,
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Vehicles retrieved successfully',
            'data'    => $vehicles,
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
