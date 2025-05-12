<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Vahicle;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function search(Request $request)
    {
        $per_page = $request->per_page ?? 100;
        $nowDate  = now()->format('Y-m-d');
        $nowTime  = now()->format('h:i A');

        $vehicles = Vahicle::with('category', 'bookings')
            ->where('name', 'LIKE', '%' . $request->search . '%')
            ->orWhere('number_plate', 'LIKE', '%' . $request->search . '%')
            ->paginate($per_page);
        $data = $vehicles->getCollection()->transform(function ($vehicle) use ($nowDate, $nowTime) {
            $all_booking_dates = $vehicle->bookings
                ->filter(function ($booking) use ($nowDate) {
                    return $booking->booking_date >= $nowDate;
                })
                ->pluck('booking_date')
                ->unique()
                ->values();

            $is_currently_booked = $vehicle->bookings->contains(function ($booking) use ($nowDate, $nowTime) {
                return $booking->booking_date === $nowDate &&
                strtotime($booking->from) <= strtotime($nowTime) &&
                strtotime($booking->to) >= strtotime($nowTime);
            });

            return [
                "id"       => $vehicle->id,
                "title"    => $vehicle->name,
                "code"     => $vehicle->number_plate,
                "category" => $vehicle->category->name ?? '',
                "image"    => $vehicle->category->icon ?? '',
                "book"     => $is_currently_booked,
                "booked"   => $all_booking_dates,
            ];
        });
        return response()->json([
            'status'  => true,
            'message' => 'Data retrieved successfully',
            'data'    => $data,
        ]);
    }

    public function statistic()
    {
        $nowDate = now()->format('Y-m-d');
        $nowTime = now()->format('h:i A');

        $vehicles      = Vahicle::with('category', 'bookings')->get();
        $total_vehicle = $vehicles->count();

        $data = $vehicles->map(function ($vehicle) use ($nowDate, $nowTime) {
            $is_booked_today = false;
            $is_booked       = false;
            $is_available    = true;

            $todayBookings = $vehicle->bookings->filter(function ($booking) use ($nowDate) {
                return $booking->booking_date === $nowDate;
            });

            $currentBooking = $todayBookings->filter(function ($booking) use ($nowTime) {
                return strtotime($booking->from) <= strtotime($nowTime)
                && strtotime($booking->to) >= strtotime($nowTime);
            });

            if ($todayBookings->isNotEmpty()) {
                $is_booked_today = true;
            }

            if ($currentBooking->isNotEmpty()) {
                $is_booked    = true;
                $is_available = false;
            }

            return [
                'id'            => $vehicle->id,
                'title'         => $vehicle->name,
                'code'          => $vehicle->number_plate,
                'category_id'   => $vehicle->category->id ?? null,
                'category_name' => $vehicle->category->name ?? '',
                'image'         => $vehicle->category->icon ?? '',
                'is_available'  => $is_available,
            ];
        });

        $available_vehicles = $data->where('is_available', true)->values();
        $available_count    = $available_vehicles->count();
        $booked_count       = $total_vehicle - $available_count;

        $available_info = $available_vehicles->groupBy('category_id')->map(function ($vehicles) {
            return [
                'category' => $vehicles->first()['category_name'],
                'count'    => $vehicles->count(),
                'image'    => $vehicles->first()['image'],
            ];
        })->values();

        return response()->json([
            'status'  => true,
            'message' => 'Data retrieved successfully',
            'data'    => [
                'total_vehicle'  => $total_vehicle,
                'available'      => $available_count,
                'booked'         => $booked_count,
                'available_info' => $available_info,
            ],
        ]);
    }

    public function searchByType(Request $request)
    {
        $nowDate = now()->format('Y-m-d');
        $nowTime = now()->format('h:i A');

        $vehicles = Vahicle::with('category', 'bookings.customer');

        if ($request->search) {
            $vehicles = $vehicles->where(function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('number_plate', 'LIKE', '%' . $request->search . '%');
            });
        }

        if ($request->category) {
            $vehicles = $vehicles->whereHas('category', function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->category . '%');
            });
        }
        $vehicles = $vehicles->get();

        $filter = $request->filter;

        $data = $vehicles->map(function ($vehicle) use ($nowDate, $nowTime) {
            $is_booked   = false;
            $renter_info = null;

            $todayBookings = $vehicle->bookings->filter(function ($booking) use ($nowDate) {
                return $booking->booking_date === $nowDate;
            });

            $currentBooking = $todayBookings->first(function ($booking) use ($nowTime) {
                return strtotime($booking->from) <= strtotime($nowTime) &&
                strtotime($booking->to) >= strtotime($nowTime);
            });

            if ($currentBooking) {
                $is_booked = true;

                $renter = $currentBooking->customer ?? null;

                $renter_info = [
                    'id'           => $renter->id ?? null,
                    'name'         => $renter->name ?? '',
                    'phone'        => $renter->phone ?? '',
                    'booking_from' => $currentBooking->from,
                    'booking_to'   => $currentBooking->to,
                ];
            }

            return [
                'id'            => $vehicle->id,
                'title'         => $vehicle->name,
                'code'          => $vehicle->number_plate,
                'category_name' => $vehicle->category->name ?? '',
                'is_booked'     => $is_booked,
                'renter_info'   => $renter_info,
            ];
        })->filter(function ($vehicle) use ($filter) {
            if ($filter === 'available') {
                return ! $vehicle['is_booked'];
            } elseif ($filter === 'booked') {
                return $vehicle['is_booked'];
            }
            return true;
        })->values();

        return response()->json([
            'status'  => true,
            'message' => 'Vehicles booking info retrieved successfully.',
            'data'    => $data,
        ]);
    }

}
