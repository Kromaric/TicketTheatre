<?php

namespace Database\Seeders;

use App\Models\Hall;
use Illuminate\Database\Seeder;

class HallSeeder extends Seeder
{
    public function run(): void
    {
        $halls = [
            [
                'name' => 'Grande Salle',
                'location' => 'Niveau 1 - Aile Est',
                'capacity' => 500,
                'description' => 'Notre plus grande salle avec une acoustique exceptionnelle',
                'type' => 'Grande salle',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1503095396549-807759245b35',
                'amenities' => ['Climatisation', 'Bar', 'Accessibilité PMR', 'Vestiaire'],
            ],
            [
                'name' => 'Petit Théâtre',
                'location' => 'Niveau 2 - Aile Ouest',
                'capacity' => 150,
                'description' => 'Salle intimiste idéale pour les spectacles intimistes',
                'type' => 'Petit théâtre',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1507676184212-d03ab07a01bf',
                'amenities' => ['Climatisation', 'Accessibilité PMR'],
            ],
            [
                'name' => 'Scène Studio',
                'location' => 'Niveau 3',
                'capacity' => 80,
                'description' => 'Espace modulable pour les créations expérimentales',
                'type' => 'Studio',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1514306191717-452ec28c7814',
                'amenities' => ['Accessibilité PMR', 'Scène modulable'],
            ],
            [
                'name' => 'Amphithéâtre',
                'location' => 'Niveau 1 - Centre',
                'capacity' => 300,
                'description' => 'Salle en gradin avec excellente visibilité',
                'type' => 'Amphithéâtre',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36',
                'amenities' => ['Climatisation', 'Bar', 'Accessibilité PMR', 'Parking'],
            ],
            [
                'name' => 'Salle Polyvalente',
                'location' => 'Niveau 2 - Aile Est',
                'capacity' => 200,
                'description' => 'Salle adaptable pour tous types de spectacles',
                'type' => 'Polyvalente',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1497366216548-37526070297c',
                'amenities' => ['Climatisation', 'Vestiaire', 'Accessibilité PMR'],
            ],
        ];

        foreach ($halls as $hall) {
            Hall::create($hall);
        }
    }
}
