<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function Pest\Laravel\json;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;
    protected $guarded = ['id'];

    public function vehicle()
    {
        return $this->belongsTo(Vahicle::class);
    }

    protected $casts = [
        'booking_dates' => 'array',
    ];

    public function getBookingDatesAttribute($dates)
    {
        return json_decode($dates, true);
    }

    public function customer(){
        return $this->belongsTo(Customer::class);
    }
}
