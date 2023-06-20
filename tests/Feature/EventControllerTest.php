<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    protected const datePattern = '/^\d{4}\-\d{2}\-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$/';

    public function testEventsList(): void
    {
        User::factory(2)
            ->has(
                Event::factory(2)
                    ->has(
                        Attendee::factory(2)
                            ->state(function (array $attr, Event $event) {
                                return [
                                    'event_id' => $event->id,
                                    'user_id' => User::factory()->create()->id,
                                ];
                            })
                    )
            )
            ->create();


        $this->getJson('/api/events')
            ->assertOk()
            ->assertJsonCount(2, 'data.0.attendees')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'startTime',
                        'endTime',
                        'user' => [
                            'id',
                            'name'
                        ],
                        'attendees' => [
                            '*' => [
                                'id',
                                'user' => [
                                    'id',
                                    'name'
                                ]
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(4, 'data')
            ->assertJson(function (AssertableJson $json) {
                $json->whereType('data.0.id', 'integer')
                    ->whereType('data.0.name', 'string')
                    ->whereType('data.0.description', 'string')
                    ->where('data.0.startTime', fn(string $value) => (bool)preg_match(self::datePattern, $value))
                    ->where('data.0.endTime', fn(string $value) => (bool)preg_match(self::datePattern, $value))
                    ->whereType('data.0.user.id', 'integer')
                    ->whereType('data.0.user.name', 'string');
            });
    }

    public function testEventStoreSuccess(): void
    {
        // TODO temporary user - remove when realize authorization
        User::factory()->create(['id' => 1]);

        $data = [
            'name' => 'My first event',
            'description' => 'long description for event here',
            'startTime' => now()->addDay()->format('Y-m-d H:i'),
            'endTime' => now()->addDays(2)->format('Y-m-d H:i'),
        ];

        $this->postJson('/api/events', $data)
            ->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'description', 'startTime', 'endTime', 'user' => ['id', 'name']
                ]
            ])
            ->assertJson(function (AssertableJson $json) {
                $json->whereType('data.id', 'integer')
                    ->whereType('data.name', 'string')
                    ->whereType('data.description', 'string')
                    ->where('data.startTime', fn(string $value) => (bool)preg_match(self::datePattern, $value))
                    ->where('data.endTime', fn(string $value) => (bool)preg_match(self::datePattern, $value))
                    ->whereType('data.user.id', 'integer')
                    ->whereType('data.user.name', 'string');
            });;
    }

    public function testEventStoreSuccessWithoutDescription(): void
    {
        // TODO temporary user - remove when realize authorization
        User::factory()->create(['id' => 1]);

        $data = [
            'name' => 'My second event',
            'startTime' => now()->addDay()->format('Y-m-d H:i'),
            'endTime' => now()->addDays(2)->format('Y-m-d H:i'),
        ];

        $this->postJson('/api/events', $data)
            ->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'description', 'startTime', 'endTime', 'user' => ['id', 'name']
                ]
            ])
            ->assertJson(function (AssertableJson $json) {
                $json->whereType('data.id', 'integer')
                    ->whereType('data.name', 'string')
                    ->whereType('data.description', 'null')
                    ->where('data.startTime', fn(string $value) => (bool)preg_match(self::datePattern, $value))
                    ->where('data.endTime', fn(string $value) => (bool)preg_match(self::datePattern, $value))
                    ->whereType('data.user.id', 'integer')
                    ->whereType('data.user.name', 'string');
            });
    }

    public function testEventStoreValidationAllEmpty(): void
    {
        $this->postJson('/api/events', [])
            ->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => ['name', 'startTime', 'endTime']
            ]);
    }

    public function testEventStoreValidationShortNameWrongDates(): void
    {
        $this->postJson('/api/events', ['name' => 'short', 'startTime' => 'abc', 'endTime' => 'abc'])
            ->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => ['name', 'startTime', 'endTime']
            ]);
    }

    public function testEventStoreValidationWrongDatesInPast(): void
    {
        $this->postJson(
            '/api/events',
            [
                'name' => 'Name is good!',
                'startTime' => now()->addDays(-2)->format('Y-m-d H:i'),
                'endTime' => now()->addDays()->format('Y-m-d H:i'),
            ])->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => ['startTime']
            ]);
    }

    public function testEventStoreValidationWrongDatesEndTimeMustBeAfterStartTine(): void
    {
        $this->postJson(
            '/api/events',
            [
                'name' => 'Name is good!',
                'startTime' => now()->addDays(2)->format('Y-m-d H:i'),
                'endTime' => now()->addDays(-2)->format('Y-m-d H:i'),
            ])->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => ['endTime']
            ]);
    }

    public function testEventShowSuccess(): void
    {
        /** @var User $user */
        $user = User::factory()->has(Event::factory())->create();
        /** @var Event $event */
        $event = $user->events()->first();

        $attendees = Attendee::factory(4)
            ->make([
                'event_id' => $event->id,
                'user_id' => User::factory()->create(),
            ]);

        $event->attendees()->saveMany($attendees);

        $this->getJson('/api/events/' . $event->id)
            ->assertOk()
            ->assertJsonCount(4, 'data.attendees')
            ->assertJson(function (AssertableJson $json) use ($event, $user) {
                $json->where('data.id', fn(int $id) => $id === $event->id)
                    ->where('data.name', fn(string $name) => $name === $event->name)
                    ->where('data.description', fn(string $desc) => $desc === $event->description)
                    ->where('data.startTime', fn(string $value) => (bool)preg_match(self::datePattern, $value))
                    ->where('data.endTime', fn(string $value) => (bool)preg_match(self::datePattern, $value))
                    ->where('data.user.id', fn(int $id) => $id === $user->id)
                    ->where('data.user.name', fn(string $name) => $name === $user->name)
                    ->whereType('data.attendees.0.id', 'integer')
                    ->whereType('data.attendees.0.user.id', 'integer')
                    ->whereType('data.attendees.0.user.name', 'string');
            });
    }

    public function testEventShowNotFound(): void
    {
        $this->getJson('/api/events/1111111111111')
            ->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    public function testEventUpdateNotFound(): void
    {
        $this->putJson('/api/events/111111111111')
            ->assertNotFound();
    }

    public function testEventUpdateSuccess(): void
    {
        $user = User::factory()->has(Event::factory())->create();
        $event = $user->events()->first();

        $this->putJson('/api/events/' . $event->id, [
            'name' => 'My updated event name',
            'description' => 'Changed description in event',
            'startTime' => $event->start_time->format('Y-m-d H:i'),
            'endTime' => $event->end_time->format('Y-m-d H:i'),
        ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $event->id,
                    'name' => 'My updated event name',
                    'description' => 'Changed description in event',
                ]
            ]);
    }

    public function testEventDeleteNotFound(): void
    {
        $this->deleteJson('/api/events/11111111')
            ->assertNotFound();
    }

    public function testEventDeleteSuccess(): void
    {
        $user = User::factory()->has(Event::factory())->create();
        $event = $user->events()->first();

        $this->deleteJson('/api/events/' . $event->id)
            ->assertNoContent();
    }
}
