<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = ['id'];

    public function vahicles()
    {
        return $this->hasMany(Vahicle::class);
    }

    public function getIconAttribute($image)
    {
        return asset('uploads/' . $image);
    }
}
