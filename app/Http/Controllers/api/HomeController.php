<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Vahicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

//     public function statistic()
//     {
//         $nowDate       = now()->format('Y-m-d');
//         $nowTime       = now()->format('h:i A');
//         $total_vehicle = Vahicle::count();
//         $vehicles      = Vahicle::with('category', 'bookings')->get();
// $data = $vehicles->transform(function ($vehicle) use ($nowDate, $nowTime) {
//     $is_booked_today = false;
//     $is_booked = false;
//     $is_available = true;

//     // আজকের সব বুকিং
//     $todayBookings = $vehicle->bookings->filter(function ($booking) use ($nowDate) {
//         return $booking->booking_date === $nowDate;
//     });

//     // বর্তমানে চলছে এমন বুকিং
//     $currentBooking = $todayBookings->filter(function ($booking) use ($nowTime) {
//         return $booking->from <= $nowTime && $booking->to >= $nowTime;
//     });

//     // স্টেট আপডেট
//     if ($todayBookings->isNotEmpty()) {
//         $is_booked_today = true;
//     }

//     if ($currentBooking->isNotEmpty()) {
//         $is_booked = true;
//         $is_available = false;
//     }

//     return [
//         "id"               => $vehicle->id,
//         "title"            => $vehicle->name,
//         "code"             => $vehicle->number_plate,
//         "category"         => $vehicle->category,
//         "book"             => $is_booked ? "true" : "false",
//         "is_booked_today"  => $is_booked_today,
//         "is_booked"        => $is_booked,
//         "is_available"     => $is_available,
//     ];
// });
// return $data;
//         // $data = $vehicles->map(function ($vehicle) use ($nowDate, $nowTime) {
//         //     $is_booked_today      = false;
//         //     $is_booked            = false;
//         //     $is_available         = true;

//             // if ($is_booked || $today_bookings_count > 0) {
//             //     $is_available = false;
//             // }

//             // return [
//             //     'id'           => $vehicle->id,
//             //     'title'        => $vehicle->name,
//             //     'code'         => $vehicle->number_plate,
//             //     'image'        => $vehicle->category->icon,
//             //     'booked_today' => $today_bookings_count,
//             //     'available'    => $is_available,
//             //     'category'     => $vehicle->category->name,
//             // ];
//         // });
// return $vehicles;
//         // $available_vehicles = $data->where('available', true)->values();
//         // $available_count    = $available_vehicles->count();
//         // $booked             = $data->where('available', false)->count();

//         // $available_info = $available_vehicles->groupBy('category')->map(function ($categoryVehicles) {
//         //     return [
//         //         'category' => $categoryVehicles->first()['category'],
//         //         'count'    => $categoryVehicles->count(),
//         //         'image'    => $categoryVehicles->first()['image'],
//         //     ];
//         // })->values()->toArray();

//         // return response()->json([
//         //     'status'  => true,
//         //     'message' => 'Data retrieved successfully',
//         //     'data'    => [
//         //         'total_vehicle'  => $total_vehicle,
//         //         'available'      => $available_count,
//         //         'booked'         => $booked,
//         //         'available_info' => $available_info,
//         //     ],
//         // ]);
//     }

    public function statistic()
    {
        $nowDate       = now()->format('Y-m-d');
        $nowTime       = now()->format('h:i A');
        $total_vehicle = Vahicle::count();

        $vehicles = Vahicle::with('category', 'bookings')->get();

        $data = $vehicles->transform(function ($vehicle) use ($nowDate, $nowTime) {
            $is_booked_today = false;
            $is_booked       = false;
            $is_available    = true;

            // আজকের সব বুকিং
            $todayBookings = $vehicle->bookings->filter(function ($booking) use ($nowDate) {
                return $booking->booking_date === $nowDate;
            });

            // বর্তমানে চলছে এমন বুকিং
            $currentBooking = $todayBookings->filter(function ($booking) use ($nowTime) {
                return $booking->from <= $nowTime && $booking->to >= $nowTime;
            });

            if ($todayBookings->isNotEmpty()) {
                $is_booked_today = true;
            }

            if ($currentBooking->isNotEmpty()) {
                $is_booked    = true;
                $is_available = false;
            }

            return [
                "id"           => $vehicle->id,
                "title"        => $vehicle->name,
                "code"         => $vehicle->number_plate,
                "category"     => $vehicle->category->name,
                "image"        => asset('uploads/category/' . $vehicle->category->icon),
                "booked_today" => $is_booked_today,
                "is_booked"    => $is_booked,
                "available"    => $is_available,
            ];
        });

        // Available and booked count
        $available_vehicles = $data->where('available', true)->values();
        $available_count    = $available_vehicles->count();
        $booked             = $data->where('available', false)->count();

        // Grouping by category
        $available_info = $available_vehicles->groupBy('category')->map(function ($categoryVehicles) {
            return [
                'category' => $categoryVehicles->first()['category'],
                'count'    => $categoryVehicles->count(),
                'image'    => $categoryVehicles->first()['image'],
            ];
        })->values()->toArray();

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

    public function searchByType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors(),
            ]);
        }

        $today        = now()->format('Y-m-d');
        $current_time = now()->format('H:i:s');

        if ($request->type == 'total') {
            $vehicles = Vahicle::with('bookings', 'category');

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

            $vehicles     = $vehicles->get();
            $today        = now()->format('Y-m-d');
            $current_time = now()->format('H:i:s');

            $data = $vehicles->map(function ($vehicle) use ($today, $current_time, $request) {
                $all_booking_dates = $vehicle->bookings->flatMap(fn($booking) => $booking->booking_dates)->unique()->toArray();

                $is_booked_today = in_array($today, $all_booking_dates);
                $is_booked       = false;
                $renter_info     = null;

                foreach ($vehicle->bookings as $booking) {
                    if ($booking->booking_type === 'multiple_day' && in_array($today, $booking->booking_dates)) {
                        $is_booked   = true;
                        $renter_info = [
                            "id"                => $booking->id,
                            "renter_name"       => $booking->renter_name,
                            "phone"             => $booking->phone_number,
                            "booking_time_from" => $booking->booking_time_from,
                            "booking_time_to"   => $booking->booking_time_to,
                        ];
                        break;
                    } elseif ($booking->booking_type === 'single_day' && in_array($today, $booking->booking_dates) && $booking->booking_time_to >= $current_time) {
                        $is_booked   = true;
                        $renter_info = [
                            "id"                => $booking->id,
                            "renter_name"       => $booking->renter_name,
                            "phone"             => $booking->phone_number,
                            "booking_time_from" => $booking->booking_time_from,
                            "booking_time_to"   => $booking->booking_time_to,
                        ];
                        break;
                    }
                }

                if (($request->filter == 'available' && $is_booked) || ($request->filter == 'booked' && ! $is_booked)) {
                    return null;
                }

                return [
                    "id"          => $vehicle->id,
                    "title"       => $vehicle->name,
                    "code"        => $vehicle->number_plate,
                    "category"    => optional($vehicle->category)->name,
                    "image"       => optional($vehicle->category)->icon,
                    "book"        => $is_booked,
                    "booked"      => array_values($all_booking_dates),
                    "renter_info" => $renter_info,
                ];
            })->filter()->values();

            return response()->json([
                'status'  => true,
                'message' => 'Total vehicles retrieved successfully.',
                'data'    => $data,
            ]);
        }

        if ($request->type == 'booked') {
            $vehicles = Vahicle::with('bookings');

            if ($request->search) {
                $vehicles = $vehicles->where(function ($query) use ($request) {
                    $query->where('name', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('number_plate', 'LIKE', '%' . $request->search . '%');
                });
            }

            $vehicles = $vehicles->get();

            $data = $vehicles->map(function ($vehicle) use ($today, $current_time, $request) {
                $all_booking_dates = $vehicle->bookings->flatMap(fn($booking) => $booking->booking_dates)->unique()->toArray();

                $is_booked_today = in_array($today, $all_booking_dates);
                $is_booked       = false;

                foreach ($vehicle->bookings as $booking) {
                    if ($booking->booking_type === 'multiple_day' && in_array($today, $booking->booking_dates)) {
                        $is_booked   = true;
                        $renter_info = [
                            "id"                => $booking->id,
                            "renter_name"       => $booking->renter_name,
                            "phone"             => $booking->phone_number,
                            "booking_time_from" => $booking->booking_time_from,
                            "booking_time_to"   => $booking->booking_time_to,
                        ];
                        break;
                    } elseif ($booking->booking_type === 'single_day' && in_array($today, $booking->booking_dates) && $booking->booking_time_to >= $current_time) {
                        $is_booked   = true;
                        $renter_info = [
                            "id"                => $booking->id,
                            "renter_name"       => $booking->renter_name,
                            "phone"             => $booking->phone_number,
                            "booking_time_from" => $booking->booking_time_from,
                            "booking_time_to"   => $booking->booking_time_to,
                        ];
                        break;
                    }
                }

                if (! $is_booked) {
                    return null;
                }

                return [
                    "id"          => $vehicle->id,
                    "title"       => $vehicle->name,
                    "code"        => $vehicle->number_plate,
                    "category"    => optional($vehicle->category)->name,
                    "image"       => optional($vehicle->category)->icon,
                    "book"        => $is_booked,
                    "booked"      => array_values($all_booking_dates),
                    "renter_info" => $renter_info,
                ];
            })->filter()->values();

            return response()->json([
                'status'  => true,
                'message' => 'Booked vehicle retrieved successfully.',
                'data'    => $data,
            ]);
        }

        if ($request->type == 'available') {
            $vehicles = Vahicle::with('bookings');
            if ($request->category) {
                $vehicles = $vehicles->whereHas('category', function ($query) use ($request) {
                    $query->where('name', $request->category);
                });
            }

            if ($request->search) {
                $vehicles = $vehicles->where(function ($query) use ($request) {
                    $query->where('name', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('number_plate', 'LIKE', '%' . $request->search . '%');
                });
            }

            $vehicles = $vehicles->get();

            $data = $vehicles->map(function ($vehicle) use ($today, $current_time, $request) {
                $all_booking_dates = $vehicle->bookings->flatMap(fn($booking) => $booking->booking_dates)->unique()->toArray();

                $is_booked_today = in_array($today, $all_booking_dates);
                $is_booked       = false;

                foreach ($vehicle->bookings as $booking) {
                    if ($booking->booking_type === 'multiple_day' && in_array($today, $booking->booking_dates)) {
                        $is_booked = true;
                        break;
                    } elseif ($booking->booking_type === 'single_day' && in_array($today, $booking->booking_dates) && $booking->booking_time_to >= $current_time) {
                        $is_booked = true;
                        break;
                    }
                }

                if ($is_booked) {
                    return null;
                }

                return [
                    "id"          => $vehicle->id,
                    "title"       => $vehicle->name,
                    "code"        => $vehicle->number_plate,
                    "category"    => optional($vehicle->category)->name,
                    "image"       => optional($vehicle->category)->icon,
                    "book"        => $is_booked,
                    "booked"      => null,
                    "renter_info" => null,
                ];
            })->filter()->values();

            return response()->json([
                'status'  => true,
                'message' => 'Available vehicle retrieved successfully.',
                'data'    => $data,
            ]);
        }

        return response()->json([
            'status'  => false,
            'message' => 'Invalid type specified.',
            'data'    => null,
        ]);
    }

}
