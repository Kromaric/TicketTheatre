<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hall extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'capacity',
        'description',
        'type',
        'is_active',
        'image_url',
        'amenities',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'amenities' => 'array',
            'capacity' => 'integer',
        ];
    }

    // Relations
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function seances()
    {
        return $this->hasMany(Seance::class);
    }
}
