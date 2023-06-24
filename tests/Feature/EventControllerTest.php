<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    protected const datePattern = '/^\d{4}\-\d{2}\-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$/';

    public function testEventsListWithoutRelation(): void
    {
        User::factory(2)
            ->has(
                Event::factory(2)
                    ->has(
                        Attendee::factory(2)
                            ->for(User::factory())
                    )
            )
            ->create();


        $this->getJson('/api/events')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'startTime',
                        'endTime',
                    ],
                ]
            ])
            ->assertJsonCount(4, 'data')
            ->assertJson(function (AssertableJson $json) {
                $json->whereType('data.0.id', 'integer')
                    ->whereType('data.0.name', 'string')
                    ->whereType('data.0.description', 'string')
                    ->where('data.0.startTime', fn(string $value) => (bool)preg_match(self::datePattern, $value))
                    ->where('data.0.endTime', fn(string $value) => (bool)preg_match(self::datePattern, $value));
            });
    }

    public function testEventStoreSuccess(): void
    {

        $data = [
            'name' => 'My first event',
            'description' => 'long description for event here',
            'startTime' => now()->addDay()->format('Y-m-d H:i'),
            'endTime' => now()->addDays(2)->format('Y-m-d H:i'),
        ];

        Sanctum::actingAs(User::factory()->create());

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
        $data = [
            'name' => 'My second event',
            'startTime' => now()->addDay()->format('Y-m-d H:i'),
            'endTime' => now()->addDays(2)->format('Y-m-d H:i'),
        ];

        Sanctum::actingAs(User::factory()->create());

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
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/events', [])
            ->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => ['name', 'startTime', 'endTime']
            ]);
    }

    public function testEventStoreValidationShortNameWrongDates(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/events', ['name' => 'short', 'startTime' => 'abc', 'endTime' => 'abc'])
            ->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => ['name', 'startTime', 'endTime']
            ]);
    }

    public function testEventStoreValidationWrongDatesInPast(): void
    {
        Sanctum::actingAs(User::factory()->create());

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
        Sanctum::actingAs(User::factory()->create());

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
        User::factory()
            ->has(
                Event::factory()
                    ->has(
                        Attendee::factory(4)
                            ->for(User::factory())
                    )
            )
            ->create();

        $event = Event::all()->first();

        $this->getJson('/api/events/' . $event->id)
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($event) {
                $json->where('data.id', fn(int $id) => $id === $event->id)
                    ->where('data.name', fn(string $name) => $name === $event->name)
                    ->where('data.description', fn(string $desc) => $desc === $event->description)
                    ->where('data.startTime', fn(string $value) => (bool)preg_match(self::datePattern, $value))
                    ->where('data.endTime', fn(string $value) => (bool)preg_match(self::datePattern, $value));
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
        Sanctum::actingAs(User::factory()->create());

        $this->putJson('/api/events/111111111111')
            ->assertNotFound();
    }

    public function testEventUpdateSuccess(): void
    {
        $user = User::factory()->has(Event::factory())->create();
        $event = $user->events->first();

        Sanctum::actingAs(User::factory()->create());

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
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson('/api/events/11111111')
            ->assertNotFound();
    }

    public function testEventDeleteSuccess(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $user = User::factory()->has(Event::factory())->create();
        $event = $user->events()->first();

        $this->deleteJson('/api/events/' . $event->id)
            ->assertNoContent();
    }
}
