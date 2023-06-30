<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\User;
use App\Notifications\EventReminderNotification;
use App\Providers\AppServiceProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;

class UserNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly Event $event,
        public readonly User  $user,
        public readonly int $tries = 1,
    )
    {
    }

    public function middleware(): array
    {
        return [
            (new RateLimited(AppServiceProvider::RATE_LIMITING_EMAIL_NOTIFY))
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user->notify(new EventReminderNotification($this->event));
    }
}
