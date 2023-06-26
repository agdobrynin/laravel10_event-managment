<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Enum\AbilityAttendeeEnum;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\User;
use App\Policies\EventPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Event::class => EventPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define(AbilityAttendeeEnum::DELETE->value, function (User $user, Attendee $attendee, Event $event) {
            return $user->id === $attendee->user_id
                || $user->id === $event->user_id;
        });
    }
}
