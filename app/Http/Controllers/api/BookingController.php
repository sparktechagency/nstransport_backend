<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function booking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id'         => 'required|numeric',
            'renter_name'        => 'required|string|max:255',
            'phone_number'       => 'required|string|max:20',
            'booking_type'       => 'required|string',
            'booked_dates' => 'required|array',
            'booking_time_from'  => ['nullable', 'regex:/^(?:2[0-3]|[01][0-9]):[0-5][0-9](?::[0-5][0-9])?$/'],
            'booking_time_to'    => ['nullable', 'regex:/^(?:2[0-3]|[01][0-9]):[0-5][0-9](?::[0-5][0-9])?$/'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors(),
            ]);
        };
        // return $request;
        $booking = Booking::create([
            'vehicle_id'         => $request->vehicle_id,
            'renter_name'        => $request->renter_name,
            'phone_number'       => $request->phone_number,
            'booking_type'       => $request->booking_type,
            'booking_dates'       => $request->booked_dates,
            'booking_time_from'  => $request->booking_time_from,
            'booking_time_to'    => $request->booking_time_to,
        ]);
        return response()->json([
            'status'  => true,
            'message' => 'Vehicle booked successfully.',
            'data'    => $booking,
        ]);
    }
}
