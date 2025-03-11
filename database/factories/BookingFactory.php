<?php
namespace Database\Factories;

use App\Models\Vahicle;
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
        $booking_types = ['single_day', 'multiple_day'];
        $vehicle_ids   = Vahicle::pluck('id')->toArray();
        $booking_type  = Arr::random($booking_types);

        // Generate multiple booked dates
        $booked_dates = [
            "2025-03-14",
            "2025-03-31",
            "2025-04-17",
            "2025-06-15",
            "2025-07-26",
            "2025-10-15",
            "2025-11-08",
        ];

        $booking_dates = [];
        for ($i = 0; $i < 3; $i++) {
            $booking_dates[] = Arr::random($booked_dates);
        }

        $bookingData = [
            "vehicle_id"    => Arr::random($vehicle_ids),
            "renter_name"   => $this->faker->name,
            "phone_number"  => $this->faker->phoneNumber,
            "booking_type"  => $booking_type,
            "booking_dates" => json_encode($booked_dates),
        ];

        if ($booking_type === 'single_day') {
            $bookingData["booking_time_from"] = $this->faker->time('H:i:s');
            $bookingData["booking_time_to"]   = $this->faker->time('H:i:s');
        }

        return $bookingData;
    }
}
