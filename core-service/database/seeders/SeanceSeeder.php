<?php

namespace Database\Seeders;

use App\Models\Seance;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SeanceSeeder extends Seeder
{
    public function run(): void
    {
        // Dates pour les prochaines semaines
        $dates = [];
        for ($i = 1; $i <= 30; $i++) {
            $dates[] = Carbon::now()->addDays($i);
        }

        $seances = [
            // Le Bourgeois Gentilhomme - Grande Salle
            ['spectacle_id' => 1, 'hall_id' => 1, 'price' => 35.00, 'days' => [1, 3, 5, 7, 9, 11], 'time' => '20:00'],
            ['spectacle_id' => 1, 'hall_id' => 1, 'price' => 30.00, 'days' => [7, 14, 21], 'time' => '15:00'],

            // Roméo et Juliette - Grande Salle
            ['spectacle_id' => 2, 'hall_id' => 1, 'price' => 45.00, 'days' => [2, 4, 6, 8, 10], 'time' => '20:30'],
            ['spectacle_id' => 2, 'hall_id' => 1, 'price' => 40.00, 'days' => [8, 15, 22], 'time' => '14:00'],

            // Les Misérables - Amphithéâtre
            ['spectacle_id' => 3, 'hall_id' => 4, 'price' => 65.00, 'days' => [5, 12, 19, 26], 'time' => '19:30'],
            ['spectacle_id' => 3, 'hall_id' => 4, 'price' => 60.00, 'days' => [7, 14, 21, 28], 'time' => '15:00'],

            // Le Lac des Cygnes - Grande Salle
            ['spectacle_id' => 4, 'hall_id' => 1, 'price' => 55.00, 'days' => [15, 16, 17, 18, 19], 'time' => '20:00'],
            ['spectacle_id' => 4, 'hall_id' => 1, 'price' => 50.00, 'days' => [17, 18, 19], 'time' => '14:30'],

            // Tartuffe - Petit Théâtre
            ['spectacle_id' => 5, 'hall_id' => 2, 'price' => 32.00, 'days' => [3, 6, 9, 12, 15, 18], 'time' => '19:00'],
            ['spectacle_id' => 5, 'hall_id' => 2, 'price' => 28.00, 'days' => [7, 14], 'time' => '16:00'],

            // HUMAN - Salle Polyvalente
            ['spectacle_id' => 6, 'hall_id' => 5, 'price' => 38.00, 'days' => [10, 11, 12, 13], 'time' => '20:30'],

            // Pierre et le Loup - Petit Théâtre
            ['spectacle_id' => 7, 'hall_id' => 2, 'price' => 18.00, 'days' => [2, 9, 16, 23], 'time' => '14:00'],
            ['spectacle_id' => 7, 'hall_id' => 2, 'price' => 18.00, 'days' => [3, 10, 17, 24], 'time' => '10:30'],

            // Gad Elmaleh - Grande Salle
            ['spectacle_id' => 8, 'hall_id' => 1, 'price' => 42.00, 'days' => [13, 14, 15], 'time' => '21:00'],
            ['spectacle_id' => 8, 'hall_id' => 1, 'price' => 42.00, 'days' => [14], 'time' => '18:00'],

            // Hamlet - Amphithéâtre
            ['spectacle_id' => 9, 'hall_id' => 4, 'price' => 48.00, 'days' => [20, 22, 24, 26, 28], 'time' => '19:30'],

            // La Belle et la Bête - Grande Salle
            ['spectacle_id' => 10, 'hall_id' => 1, 'price' => 52.00, 'days' => [25, 26, 27, 28, 29], 'time' => '20:00'],
            ['spectacle_id' => 10, 'hall_id' => 1, 'price' => 48.00, 'days' => [26, 27, 28], 'time' => '15:00'],
        ];

        foreach ($seances as $seanceData) {
            foreach ($seanceData['days'] as $day) {
                if ($day <= 30) {
                    $date = Carbon::now()->addDays($day)->format('Y-m-d') . ' ' . $seanceData['time'];
                    
                    // Récupérer la capacité de la salle
                    $hall = \App\Models\Hall::find($seanceData['hall_id']);
                    
                    Seance::create([
                        'spectacle_id' => $seanceData['spectacle_id'],
                        'hall_id' => $seanceData['hall_id'],
                        'date_seance' => $date,
                        'available_seats' => $hall->capacity,
                        'price' => $seanceData['price'],
                        'status' => 'scheduled',
                    ]);
                }
            }
        }
    }
}
