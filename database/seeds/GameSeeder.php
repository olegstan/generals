<?php

use Illuminate\Database\Seeder;
use App\Models\GameVersions\Game1;
use App\Models\Game;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Game::getQuery()->delete();

        $game = Game1::create([
            'current_user_id' => 1,
            'data' => [
                'map' => [
                    'land' => 100
                ],
                'users' => [
                    1 => [
                        'land' => 10,
                        'money' => 1000,
                        'sown' => 0,
                        'solders' => 0,
                        'scientists' => 0,
                        'population' => 10,
                        'science' => [
                            'army' => [
                                'level' => 0,
                                'progress' => 0
                            ]
                        ]
                    ]
                ]
            ],
        ]);

        $game = Game::getInstanceById($game->id);
    }
}
