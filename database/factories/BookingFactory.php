<?php
namespace Database\Factories;

use App\Models\Customer;
use App\Models\Vahicle;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vehicle_ids  = Vahicle::pluck('id')->toArray();
        $customer_ids = Customer::pluck('id')->toArray();
        $fromTime     = Carbon::createFromTime(rand(6, 18), rand(0, 59));
        $toTime       = (clone $fromTime)->addHours(rand(1, 4))->addMinutes(rand(0, 59));
        $date         = Carbon::now()->addDays(rand(1, 30))->format('Y-m-d');
        return [
            'vehicle_id'   => Arr::random($vehicle_ids),
            'customer_id'  => Arr::random($customer_ids),
            'booking_date' => $date,
            'from'         => $fromTime->format('h:i A'),
            'to'           => $toTime->format('h:i A'),
        ];
    }

}
