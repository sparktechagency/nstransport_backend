<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Vahicle;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function statistic()
    {
        $today         = now()->format('Y-m-d');
        $current_time  = now()->format('H:i:s');
        $total_vehicle = Vahicle::count();
        $vehicles      = Vahicle::with('category', 'bookings')->get();

        $data = $vehicles->map(function ($vehicle) use ($today, $current_time) {
            $is_booked_today      = false;
            $is_booked            = false;
            $is_available         = true;
            $today_bookings_count = 0;

            foreach ($vehicle->bookings as $booking) {
                if ($booking->booking_type === 'multiple_day') {
                    $booking_dates = $booking->booking_dates;
                    if (in_array($today, $booking_dates)) {
                        $today_bookings_count++;
                    }
                } elseif ($booking->booking_type === 'single_day') {
                    if ($booking->booking_time_to >= $current_time) {
                        $is_booked = true;
                    } else {
                        $is_booked = false;
                    }
                }
            }

            if ($is_booked || $today_bookings_count > 0) {
                $is_available = false;
            }

            return [
                'id'           => $vehicle->id,
                'title'        => $vehicle->name,
                'code'         => $vehicle->number_plate,
                'image'        => $vehicle->category->icon,
                'booked_today' => $today_bookings_count,
                'available'    => $is_available,
                'category'     => $vehicle->category->name,
            ];
        });

        $available_vehicles = $data->where('available', true)->values();
        $available_count    = $available_vehicles->count();
        $booked             = $data->where('available', false)->count();

        $available_info = $available_vehicles->groupBy('category')->map(function ($categoryVehicles) {
            return [
                'count' => $categoryVehicles->count(),
                'image' => $categoryVehicles->first()['image'],
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Data retrieved successfully',
            'data'    => [
                'total_vehicle'  => $total_vehicle,
                'available'      => $available_count,
                'booked'         => $booked,
                'available_info' => $available_info,
            ],
        ]);
    }

    public function search(Request $request)
    {
        $per_page     = $request->per_page ?? 10;
        $today        = now()->format('Y-m-d');
        $current_time = now()->format('H:i:s');

        $vehicles = Vahicle::with('category', 'bookings')
            ->where('name', 'LIKE', '%' . $request->search . '%')
            ->orWhere('number_plate', 'LIKE', '%' . $request->search . '%')
            ->paginate($per_page);

        $data = $vehicles->map(function ($vehicle) use ($today, $current_time) {
            $booking_dates = $vehicle->bookings->map(function ($booking) {
                return $booking->booking_dates;
            })->flatten()->unique()->toArray();

            $is_booked_today = in_array($today, $booking_dates);
            $is_booked       = false;

            foreach ($vehicle->bookings as $booking) {
                if ($booking->booking_type === 'multiple_day') {
                    $booking_dates = $booking->booking_dates;
                    if (in_array($today, $booking_dates)) {
                        $is_booked = true;
                    } else {
                        $is_booked = false;
                    }
                } elseif ($booking->booking_type === 'single_day') {
                    if ($booking->booking_time_to >= $current_time) {
                        $is_booked = true;
                    } else {
                        $is_booked = false;
                    }
                }
            }

            return [
                "id"       => $vehicle->id,
                "title"    => $vehicle->name,
                "code"     => $vehicle->number_plate,
                'category' => $vehicle->category->name,
                'image'    => $vehicle->category->icon,
                'book'     => $is_booked,
                'booked'   => $booking_dates,
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Data retrieved successfully',
            'data'    => $data,
        ]);
    }
    public function searchByType(Request $request)
    {
        $per_page     = $request->per_page ?? 10;
        $today        = now()->format('Y-m-d');
        $current_time = now()->format('H:i:s');

        $vehicles = Vahicle::with('category', 'bookings')
            ->where('name', 'LIKE', '%' . $request->search . '%')
            ->orWhere('number_plate', 'LIKE', '%' . $request->search . '%')
            ->paginate($per_page);

        $data = $vehicles->map(function ($vehicle) use ($today, $current_time) {
            $booking_dates = $vehicle->bookings->map(function ($booking) {
                return $booking->booking_dates;
            })->flatten()->unique()->toArray();

            $is_booked_today = in_array($today, $booking_dates);
            $is_booked       = false;

            foreach ($vehicle->bookings as $booking) {
                if ($booking->booking_type === 'multiple_day') {
                    $booking_dates = $booking->booking_dates;
                    if (in_array($today, $booking_dates)) {
                        $is_booked = true;
                    } else {
                        $is_booked = false;
                    }
                } elseif ($booking->booking_type === 'single_day') {
                    if ($booking->booking_time_to >= $current_time) {
                        $is_booked = true;
                    } else {
                        $is_booked = false;
                    }
                }
            }

            return [
                "id"       => $vehicle->id,
                "title"    => $vehicle->name,
                "code"     => $vehicle->number_plate,
                'category' => $vehicle->category->name,
                'image'    => $vehicle->category->icon,
                'book'     => $is_booked,
                'booked'   => $booking_dates,
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Data retrieved successfully',
            'data'    => $data,
        ]);
    }

}
