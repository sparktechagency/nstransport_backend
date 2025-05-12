<?php
namespace App\Http\Controllers\api;

use Exception;
use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function booking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id'   => 'required|numeric',
            'renter_name'  => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'booked_dates' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors(),
            ]);
        }

        $customer = Customer::create([
            'name'  => $request->renter_name,
            'phone' => $request->phone_number,
        ]);

        $latestBooking = null;

        foreach ($request->booked_dates as $dates) {
            $latestBooking = Booking::create([
                'vehicle_id'   => $request->vehicle_id,
                'customer_id'  => $customer->id,
                'booking_date' => $dates['date'],
                'from'         => $dates['from'],
                'to'           => $dates['to'],
            ]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Vehicle booked successfully.',
            'data'    => $latestBooking,
        ]);
    }

    public function bookingCancle($id)
    {
        $booking = Booking::find($id);
        if (! $booking) {
            return response()->json([
                'status'  => false,
                'message' => 'The booking you are searching for does not exist',
                'data'    => null,
            ]);
        }
        $booking->delete();
        return response()->json([
            'status'  => true,
            'message' => 'Booking cancle successfully.',
            'data'    => $booking,
        ]);
    }

    public function bookingUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required',
            'from' => 'required',
            'to'   => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors(),
            ]);
        }

        $booking = Booking::find($id);

        if (! $booking) {
            return response()->json([
                'status'  => false,
                'message' => 'The booking you are searching for does not exist',
                'data'    => null,
            ]);
        }
        $booking->update([
            'booking_date' => $request->date,
            'from'         => $request->from,
            'to'           => $request->to,
        ]);
        return response()->json([
            'status'  => true,
            'message' => 'Booking updated successfully.',
            'data'    => $booking,
        ]);
    }

    public function multipleBookingUpdate(Request $request, $customer_id)
    {
        $validator = Validator::make($request->all(), [
            'renter_name'  => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors(),
            ]);
        }

        try {
            $customer = Customer::findOrFail($customer_id);
            $customer->update([
                'name'  => $request->renter_name,
                'phone' => $request->phone_number,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Customer updated successfully.',
                'data'    => $customer,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'No data found.',
                'data'    => null,
            ]);
        }
    }

public function vehicleBookingList(Request $request, $id)
{
    $nowDate = now()->format('Y-m-d');
    $nowTime = now()->format('h:i A');

    $bookings = Booking::with('customer')
        ->where('vehicle_id', $id)
        ->get()
        ->filter(function ($booking) use ($nowDate, $nowTime) {
            if ($booking->booking_date === $nowDate) {
                $bookingTo   = Carbon::parse($booking->to);
                $currentTime = Carbon::parse($nowTime);
                return $bookingTo->gte($currentTime);
            }
            return $booking->booking_date > $nowDate;
        })
        ->sortBy([
            ['booking_date', 'asc'],
            ['from', 'asc'],
        ])
        ->values();

    return response()->json([
        'status'  => true,
        'message' => 'Vehicle booking list.',
        'data'    => $bookings,
    ]);
}

}
