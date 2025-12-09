<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spectacle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'duration',
        'base_price',
        'image_url',
        'poster_url',
        'trailer_url',
        'language',
        'age_restriction',
        'category_id',
        'director',
        'actors',
        'is_published',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'duration' => 'integer',
            'base_price' => 'decimal:2',
            'age_restriction' => 'integer',
            'actors' => 'array',
            'is_published' => 'boolean',
        ];
    }

    // Relations
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function seances()
    {
        return $this->hasMany(Seance::class);
    }

    public function reservations()
    {
        return $this->hasManyThrough(Reservation::class, Seance::class);
    }
}
