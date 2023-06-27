<?php

namespace App\Console\Commands;

use App\Enum\EventLoadRelationEnum;
use App\Models\Attendee;
use App\Models\Event;
use App\Notifications\EventReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-event-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email to all event attendees that event starts soon.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /** @var Collection<Event> $events */
        $events = Event::with(EventLoadRelationEnum::ATTENDEES_USER->value)
            ->whereBetween('start_time', [now(), now()->addDay()])
            ->get();

        $count = $events->count();

        $this->info("Found {$count} " . Str::plural('event', $count));

        foreach ($events as $event) {
            /** @var Attendee $attendee */
            foreach ($event->attendees as $attendee) {
                $attendee->user->notify(new EventReminderNotification($event));
            }

            $attendeeCount = $event->attendees->count();
            $this->info("Notify about event \"{$event->name}\" was sent for {$attendeeCount} ".Str::plural('user', $attendeeCount));
        }
    }
}
