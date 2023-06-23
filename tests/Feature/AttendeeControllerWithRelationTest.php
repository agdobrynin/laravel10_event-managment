<?php


use App\Models\Attendee;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AttendeeControllerWithRelationTest extends TestCase
{
    use RefreshDatabase;

    protected const datePattern = '/^\d{4}\-\d{2}\-\d{2}T\d{2}:\d{2}:\d{2}/';

    /**
     * A basic feature test example.
     */
    public function testAttendeesFromEventWithRationsSuccess(): void
    {
        $event = Event::factory()
            ->for(User::factory())
            ->has(
                Attendee::factory(18)->for(User::factory())
            )
            ->create();

        $this->getJson("/api/events/{$event->id}/attendees?relation[]=user")
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user' => [
                            'id',
                            'name'
                        ],
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
                    ->whereType('data.0.user.id', 'integer')
                    ->whereType('data.0.user.name', 'string')
                    ->where('data.0.createdAt', fn(string $value) => (bool)preg_match(self::datePattern, $value))
                    ->where('data.0.updatedAt', fn(string $value) => (bool)preg_match(self::datePattern, $value))
                    ->etc();
            });

    }

    public function testAttendeesFromEventSuccessWithRelationAndPaginate(): void
    {
        $event = Event::factory()
            ->for(User::factory())
            ->has(
                Attendee::factory(18)->for(User::factory())
            )
            ->create();

        $this->getJson("/api/events/{$event->id}/attendees?relation[]=user&page=2")
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user' => [
                            'id',
                            'name',
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    public function testAttendeeShowWithRelationSuccess(): void
    {
        $event = Event::factory()
            ->for(User::factory())
            ->has(
                Attendee::factory()->for(User::factory())
            )
            ->create();

        $attendee = $event->attendees->first();

        $this->getJson("/api/events/{$event->id}/attendees/{$attendee->id}?relation[]=user")
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'user' => ['id', 'name'], 'createdAt', 'updatedAt']
            ])->assertJson(function (AssertableJson $json) use ($attendee) {
                $json->where('data.id', fn(int $id) => $id === $attendee->id)
                    ->where('data.user.id', fn(int $id) => $id === $attendee->user->id)
                    ->where('data.createdAt', fn(string $value) => (bool)preg_match(self::datePattern, $value))
                    ->where('data.updatedAt', fn(string $value) => (bool)preg_match(self::datePattern, $value));
            });
    }

    public function testAttendeeShowWithRelationValidationError(): void
    {
        $event = Event::factory()
            ->for(User::factory())
            ->has(
                Attendee::factory()->for(User::factory())
            )
            ->create();

        $attendee = $event->attendees->first();

        $this->getJson("/api/events/{$event->id}/attendees/{$attendee->id}?relation[]=abc&some=data&page=20")
            ->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => ['relation.0']
            ]);
    }
}
