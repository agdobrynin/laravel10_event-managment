<?php


use App\Models\Attendee;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventControllerWithRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function testEventsListWithRelationValidationError(): void
    {
        Event::factory(2)
            ->for(User::factory())
            ->has(User::factory(4))
            ->create();

        $this->getJson('/api/events?relation[]=not-valid&relation[]=not-valid-2&with_count[]=id')
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors' => ['relation.0', 'relation.1', 'with_count.0']]);
    }

    public function testEventsListWithRelation(): void
    {
        User::factory(2)
            ->has(
                Event::factory()
                    ->has(
                        Attendee::factory(2)
                            ->for(User::factory())
                    )
            )->create();

        $this->getJson('/api/events?relation[]=user&relation[]=attendees&relation[]=attendees.user&with_count[]=attendees')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonCount(2, 'data.0.attendees')
            ->assertJsonPath('data.0.countAttendees', 2)
            ->assertJsonCount(2, 'data.1.attendees')
            ->assertJsonPath('data.1.countAttendees', 2)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'startTime',
                        'endTime',
                        'user' => ['id', 'name'],
                        'attendees' => [
                            '*' => [
                                'id',
                                'createdAt',
                                'updatedAt',
                                'user' => ['id', 'name']
                            ]
                        ],
                        'countAttendees',
                        'createdAt',
                        'updatedAt',
                    ]
                ]
            ]);
    }

    public function testEventShowWithRelationValidatedError(): void
    {
        $event = Event::factory()
            ->for(User::factory())
            ->has(
                Attendee::factory(2)
                    ->for(
                        User::factory()
                    )
            )
            ->create();

        $this->getJson("/api/events/{$event->id}?relation[]=abc&with_count[]=ppp")
            ->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => ['relation.0', 'with_count.0']
            ]);
    }

    public function testEventShowWithRelation(): void
    {
        $event = Event::factory()
            ->for(User::factory())
            ->has(
                Attendee::factory(2)
                    ->for(
                        User::factory()
                    )
            )
            ->create();

        $this->getJson("/api/events/{$event->id}?relation[]=user&relation[]=attendees&relation[]=attendees.user&with_count[]=attendees")
            ->assertOk()
            ->assertJsonCount(2, 'data.attendees')
            ->assertJson([
                'data' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'startTime' => $event->start_time->format(DateTimeInterface::ATOM),
                    'endTime' => $event->end_time->format(DateTimeInterface::ATOM),
                    'user' => [
                        'id' => $event->user->id,
                        'name' => $event->user->name,
                    ],
                    'attendees' => [
                        '0' => [
                            'id' => $event->attendees->first()->id,
                            'user' => [
                                'id' => $event->attendees->first()->user->id,
                                'name' => $event->attendees->first()->user->name,
                            ],
                        ]
                    ],
                    'countAttendees' => 2,
                ]
            ]);
    }
}
