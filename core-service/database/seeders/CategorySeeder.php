<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Comédie',
                'slug' => 'comedie',
                'description' => 'Spectacles humoristiques et légers',
                'icon' => '😄',
                'color' => '#FFD700',
            ],
            [
                'name' => 'Drame',
                'slug' => 'drame',
                'description' => 'Pièces dramatiques et intenses',
                'icon' => '🎭',
                'color' => '#8B0000',
            ],
            [
                'name' => 'Musical',
                'slug' => 'musical',
                'description' => 'Comédies musicales et spectacles chantés',
                'icon' => '🎵',
                'color' => '#FF1493',
            ],
            [
                'name' => 'Danse',
                'slug' => 'danse',
                'description' => 'Spectacles de danse contemporaine et classique',
                'icon' => '💃',
                'color' => '#9370DB',
            ],
            [
                'name' => 'Classique',
                'slug' => 'classique',
                'description' => 'Théâtre classique et répertoire',
                'icon' => '📚',
                'color' => '#2F4F4F',
            ],
            [
                'name' => 'Contemporain',
                'slug' => 'contemporain',
                'description' => 'Créations contemporaines et expérimentales',
                'icon' => '🎨',
                'color' => '#00CED1',
            ],
            [
                'name' => 'Jeune Public',
                'slug' => 'jeune-public',
                'description' => 'Spectacles pour enfants et familles',
                'icon' => '👶',
                'color' => '#FF6347',
            ],
            [
                'name' => 'One-Man-Show',
                'slug' => 'one-man-show',
                'description' => 'Spectacles humoristiques en solo',
                'icon' => '🎤',
                'color' => '#FF8C00',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
