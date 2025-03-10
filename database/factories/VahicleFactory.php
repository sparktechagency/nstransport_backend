<?php
namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vahicle>
 */
class VahicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = Category::pluck('id')->toArray();
        $car_names  = [
            "SpeedHaul", "RapidTow", "TurboCarrier", "SwiftHauler", "SprintLoad", "AutoGlide", "RoadMaster", "HaulXpress", "TrackShift", "PowerTrek", "VelocityHaul", "LoadRunner", "GearLift", "DriveSwift", "CaravanKing",
        ];
        $numberPlates = ["AB12 XYZ", "XY67 JKL", "MN45 QRS", "CD89 UVW", "EF23 LMN", "GH56 PQR", "IJ78 STU", "KL90 VWX", "OP34 YZA", "QR12 BCD", "ST56 EFG", "UV78 HIJ", "WX90 KLM", "YZ23 NOP", "BC45 QRT",
        ];

        return [
            'category_id'  => Arr::random($categories),
            'name'         => Arr::random($car_names),
            'number_plate' => Arr::random($numberPlates),
        ];
    }
}
