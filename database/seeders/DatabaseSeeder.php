<?php

namespace Database\Seeders;

use App\Models\Sport;
use App\Models\User;
use App\Models\GroupChat;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {


        User::factory()->createMany([
            [
                'name' => 'Admin Account',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'role' => 'admin',
            ],
            [
                'name' => 'Coach Account',
                'email' => 'coach@example.com',
                'email_verified_at' => now(),
                'role' => 'coach',
            ],
            [
                'name' => 'Athlete Account',
                'email' => 'athlete@example.com',
                'email_verified_at' => now(),
                'role' => 'athlete',
            ],
        ]);

        // User::factory(11)->create();

        //create default sports;
        $sports = Sport::factory()->createMany([
            [
                'name' => 'Volleyball',
                'descriptions' => 'A team sport played with a ball and a net, where two teams of six players aim to score points by hitting the ball over the net and into the opposing team’s court.',
            ],
            [
                'name' => 'Basketball',
                'descriptions' => 'A fast-paced team sport played on a court, where two teams of five players compete to score points by shooting a ball into the opponent’s hoop.',
            ],
        ]);

        // Create group chats for each sport
        foreach ($sports as $sport) {
            GroupChat::create([
                'sport_id' => $sport->id,
            ]);
        }

         // Assign users to group chats
        //  $coach = User::where('role', 'coach')->first();
        //  $athlete = User::where('role', 'athlete')->first();
        //  $groupChats = GroupChat::all();

        //  foreach ($groupChats as $groupChat) {
        //      $groupChat->users()->attach([$coach->id, $athlete->id]);
        //  }


    }
}
