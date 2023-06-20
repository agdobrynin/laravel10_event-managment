<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(50)
            ->has(Event::factory())
            ->create();

        User::factory(100)->create();

        // Make attendees user for events
        $users = User::all();
        /** @var Event $event */
        foreach (Event::all() as $event) {
            $attendees = $users->random(rand(2, 20));
            foreach ($attendees as $attendee) {
                Attendee::create(['user_id' => $attendee->id, 'event_id' => $event->id]);
            }
        }
    }
}
