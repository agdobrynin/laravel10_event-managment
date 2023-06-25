<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendeeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected const datePattern = '/^\d{4}\-\d{2}\-\d{2}T\d{2}:\d{2}:\d{2}/';

    /**
     * A basic feature test example.
     */
    public function testAttendeesFromEventSuccess(): void
    {
        $event = $this->makeEventWithAttendees(18);

        $this->getJson("/api/events/{$event->id}/attendees")
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'createdAt',
                        'updatedAt',
                    ]
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links' => [
                        '*' => [
                            'url',
                            'label',
                            'active',
                        ]
                    ],
                    'path',
                    'per_page',
                    'to',
                    'total',
                ]
            ])
            ->assertJsonPath('meta.total', 18)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonCount(15, 'data')
            ->assertJson(function (AssertableJson $json) {
                $json->whereType('data.0.id', 'integer')
                    ->where('data.0.createdAt', fn(string $value) => (bool)preg_match(self::datePattern, $value))
                    ->where('data.0.updatedAt', fn(string $value) => (bool)preg_match(self::datePattern, $value))
                    ->etc();
            });

    }

    public function testAttendeesFromEventSuccessWithPaginate(): void
    {
        $event = $this->makeEventWithAttendees(18);

        $this->getJson("/api/events/{$event->id}/attendees?page=2")
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function testAttendeeShowNotBelongToEventNotFound(): void
    {
        $eventFirst = $this->makeEventWithAttendees(1);
        $eventSecond = $this->makeEventWithAttendees(1);

        $attendeeFromEventSecond = $eventSecond->attendees->first();

        $this->getJson("/api/events/{$eventFirst->id}/attendees/{$attendeeFromEventSecond->id}")
            ->assertNotFound();
    }

    public function testAttendeeShowSuccess(): void
    {
        $event = $this->makeEventWithAttendees(1);
        $attendee = $event->attendees->first();

        $this->getJson("/api/events/{$event->id}/attendees/{$attendee->id}")
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'createdAt', 'updatedAt']
            ])->assertJson(function (AssertableJson $json) use ($attendee) {
                $json->where('data.id', fn(int $id) => $id === $attendee->id)
                    ->where('data.createdAt', fn(string $value) => (bool)preg_match(self::datePattern, $value))
                    ->where('data.updatedAt', fn(string $value) => (bool)preg_match(self::datePattern, $value));
            });
    }

    public function testAttendeeStoreSuccess(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $event = Event::factory()->for(User::factory())->create();
        $this->postJson("/api/events/{$event->id}/attendees")
            ->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'user' => ['id', 'name']]
            ]);
    }

    public function testAttendeeStoreNotFound(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/events/11111111111111/attendees")
            ->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    public function testAttendeeDestroyNotFound(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/events/11111111111111/attendees/1111111")
            ->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    public function testAttendeeDestroyForbidden(): void
    {
        $event = $this->makeEventWithAttendees(1);
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/events/{$event->id}/attendees/{$event->attendees()->first()->id}")
            ->assertForbidden();
    }

    public function testAttendeeDestroyAttendeeNotBelongToEventNotFound(): void
    {
        $eventOwner = User::factory()->create();

        $event1 = Event::factory()->for($eventOwner)
            ->has(Attendee::factory()->for($eventOwner))
            ->create();

        $event2 = Event::factory()->for($eventOwner)
            ->has(Attendee::factory()->for($eventOwner))
            ->create();

        Sanctum::actingAs($eventOwner);

        $this->deleteJson("/api/events/{$event1->id}/attendees/{$event2->attendees()->first()->id}")
            ->assertNotFound();
    }

    public function testAttendeeDestroySuccessByEventOwner(): void
    {
        $event = $this->makeEventWithAttendees(1);
        Sanctum::actingAs($event->user);

        $this->deleteJson("/api/events/{$event->id}/attendees/{$event->attendees()->first()->id}")
            ->assertNoContent();
    }

    public function testAttendeeDestroySuccessByAttendee(): void
    {
        $event = $this->makeEventWithAttendees(1);
        Sanctum::actingAs($event->attendees()->first()->user);

        $this->deleteJson("/api/events/{$event->id}/attendees/{$event->attendees()->first()->id}")
            ->assertNoContent();
    }

    protected function makeEventWithAttendees(int $attendeeCount): Event
    {
        return Event::factory()
            ->for(User::factory())
            ->has(
                Attendee::factory($attendeeCount)
                    ->for(User::factory())
            )
            ->create();
    }
}
