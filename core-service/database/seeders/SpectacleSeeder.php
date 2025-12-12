<?php

namespace Database\Seeders;

use App\Models\Spectacle;
use Illuminate\Database\Seeder;

class SpectacleSeeder extends Seeder
{
    public function run(): void
    {
        $spectacles = [
            [
                'title' => 'Le Bourgeois Gentilhomme',
                'description' => 'Comédie-ballet de Molière. Monsieur Jourdain, bourgeois enrichi, rêve de devenir gentilhomme et se fait berner par des parasites.',
                'duration' => 120,
                'base_price' => 35.00,
                'image_url' => 'https://images.unsplash.com/photo-1507676184212-d03ab07a01bf',
                'poster_url' => 'https://images.unsplash.com/photo-1514306191717-452ec28c7814',
                'language' => 'fr',
                'age_restriction' => 7,
                'category_id' => 1, // Comédie
                'director' => 'Jean-Pierre Vincent',
                'actors' => ['Michel Bouquet', 'Catherine Hiegel', 'Roland Bertin'],
                'is_published' => true,
                'status' => 'ongoing',
            ],
            [
                'title' => 'Roméo et Juliette',
                'description' => 'La tragédie intemporelle de Shakespeare revisitée dans une mise en scène moderne et épurée.',
                'duration' => 150,
                'base_price' => 45.00,
                'image_url' => 'https://images.unsplash.com/photo-1503095396549-807759245b35',
                'poster_url' => 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7',
                'language' => 'fr',
                'age_restriction' => 12,
                'category_id' => 2, // Drame
                'director' => 'Thomas Ostermeier',
                'actors' => ['Niels Schneider', 'Adèle Exarchopoulos'],
                'is_published' => true,
                'status' => 'ongoing',
            ],
            [
                'title' => 'Les Misérables',
                'description' => 'La comédie musicale adaptée du chef-d\'œuvre de Victor Hugo. Une fresque épique sur la rédemption et l\'amour.',
                'duration' => 180,
                'base_price' => 65.00,
                'image_url' => 'https://images.unsplash.com/photo-1507676184212-d03ab07a01bf',
                'poster_url' => 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7',
                'trailer_url' => 'https://www.youtube.com/watch?v=example',
                'language' => 'fr',
                'age_restriction' => 10,
                'category_id' => 3, // Musical
                'director' => 'Laurence Connor',
                'actors' => ['Yvan Dautin', 'Alexia Monduit', 'Ramin Karimloo'],
                'is_published' => true,
                'status' => 'ongoing',
            ],
            [
                'title' => 'Le Lac des Cygnes',
                'description' => 'Ballet classique de Tchaïkovski. Une histoire d\'amour intemporelle portée par une chorégraphie majestueuse.',
                'duration' => 130,
                'base_price' => 55.00,
                'image_url' => 'https://images.unsplash.com/photo-1508807526345-15e9b5f4eaff',
                'poster_url' => 'https://images.unsplash.com/photo-1518834107812-67b0b7c58434',
                'language' => 'fr',
                'age_restriction' => 0,
                'category_id' => 4, // Danse
                'director' => 'Rudolf Noureev',
                'actors' => ['Marie-Agnès Gillot', 'Mathieu Ganio'],
                'is_published' => true,
                'status' => 'upcoming',
            ],
            [
                'title' => 'Tartuffe',
                'description' => 'Comédie de Molière mettant en scène un imposteur manipulant une famille bourgeoise.',
                'duration' => 110,
                'base_price' => 32.00,
                'image_url' => 'https://images.unsplash.com/photo-1503095396549-807759245b35',
                'poster_url' => 'https://images.unsplash.com/photo-1514306191717-452ec28c7814',
                'language' => 'fr',
                'age_restriction' => 12,
                'category_id' => 5, // Classique
                'director' => 'Stéphane Braunschweig',
                'actors' => ['Denis Podalydès', 'Martine Chevallier'],
                'is_published' => true,
                'status' => 'ongoing',
            ],
            [
                'title' => 'HUMAN',
                'description' => 'Spectacle de danse contemporaine explorant la condition humaine à travers des mouvements expressifs.',
                'duration' => 90,
                'base_price' => 38.00,
                'image_url' => 'https://images.unsplash.com/photo-1508807526345-15e9b5f4eaff',
                'poster_url' => 'https://images.unsplash.com/photo-1518834107812-67b0b7c58434',
                'language' => 'fr',
                'age_restriction' => 8,
                'category_id' => 6, // Contemporain
                'director' => 'Angelin Preljocaj',
                'actors' => ['Compagnie Preljocaj'],
                'is_published' => true,
                'status' => 'upcoming',
            ],
            [
                'title' => 'Pierre et le Loup',
                'description' => 'Conte musical de Prokofiev raconté et joué en direct. Idéal pour découvrir les instruments de l\'orchestre.',
                'duration' => 60,
                'base_price' => 18.00,
                'image_url' => 'https://images.unsplash.com/photo-1503095396549-807759245b35',
                'poster_url' => 'https://images.unsplash.com/photo-1507676184212-d03ab07a01bf',
                'language' => 'fr',
                'age_restriction' => 4,
                'category_id' => 7, // Jeune Public
                'director' => 'François Morel',
                'actors' => ['François Morel', 'Orchestre National'],
                'is_published' => true,
                'status' => 'ongoing',
            ],
            [
                'title' => 'Gad Elmaleh - Sans Tambour',
                'description' => 'Le nouveau spectacle de Gad Elmaleh, plein d\'humour et d\'autodérision.',
                'duration' => 100,
                'base_price' => 42.00,
                'image_url' => 'https://images.unsplash.com/photo-1485846234645-a62644f84728',
                'poster_url' => 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7',
                'trailer_url' => 'https://www.youtube.com/watch?v=example',
                'language' => 'fr',
                'age_restriction' => 12,
                'category_id' => 8, // One-Man-Show
                'director' => 'Gad Elmaleh',
                'actors' => ['Gad Elmaleh'],
                'is_published' => true,
                'status' => 'ongoing',
            ],
            [
                'title' => 'Hamlet',
                'description' => 'La tragédie de Shakespeare dans une adaptation sombre et moderne questionnant la folie et la vengeance.',
                'duration' => 160,
                'base_price' => 48.00,
                'image_url' => 'https://images.unsplash.com/photo-1503095396549-807759245b35',
                'poster_url' => 'https://images.unsplash.com/photo-1514306191717-452ec28c7814',
                'language' => 'fr',
                'age_restriction' => 14,
                'category_id' => 2, // Drame
                'director' => 'Peter Brook',
                'actors' => ['Guillaume Gallienne', 'Éric Ruf'],
                'is_published' => true,
                'status' => 'upcoming',
            ],
            [
                'title' => 'La Belle et la Bête',
                'description' => 'Comédie musicale féérique adaptée du conte classique. Magie, romance et chansons entraînantes.',
                'duration' => 120,
                'base_price' => 52.00,
                'image_url' => 'https://images.unsplash.com/photo-1507676184212-d03ab07a01bf',
                'poster_url' => 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7',
                'trailer_url' => 'https://www.youtube.com/watch?v=example',
                'language' => 'fr',
                'age_restriction' => 5,
                'category_id' => 3, // Musical
                'director' => 'Bill Condon',
                'actors' => ['Emma Watson', 'Dan Stevens'],
                'is_published' => true,
                'status' => 'upcoming',
            ],
        ];

        foreach ($spectacles as $spectacle) {
            Spectacle::create($spectacle);
        }
    }
}
