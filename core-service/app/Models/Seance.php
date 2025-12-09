<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seance extends Model
{
    use HasFactory;

    protected $fillable = [
        'spectacle_id',
        'hall_id',
        'date_seance',
        'available_seats',
        'price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_seance' => 'datetime',
            'available_seats' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    // Relations
    public function spectacle()
    {
        return $this->belongsTo(Spectacle::class);
    }

    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
