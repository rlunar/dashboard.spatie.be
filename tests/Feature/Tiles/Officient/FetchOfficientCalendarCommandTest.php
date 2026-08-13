<?php

namespace Tests\Feature\Tiles\Officient;

use Tests\TestCase;
use App\Services\Officient\Officient;
use App\Tiles\Officient\OfficientStore;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class FetchOfficientCalendarCommandTest extends TestCase
{
    use RefreshDatabase;

    public function testItExcludesPeopleWithNoScheduledMinutesOrTimeOffEvents(): void
    {
        $this->travelTo(Carbon::parse('2026-08-13 10:00', 'Europe/Brussels'));

        $officient = Mockery::mock(Officient::class);

        $officient->shouldReceive('getPeople')
            ->once()
            ->andReturn(collect([
                ['id' => 1, 'name' => 'Nico', 'email' => 'nico@example.com'],
                ['id' => 2, 'name' => 'Marceli', 'email' => 'marceli@example.com'],
                ['id' => 3, 'name' => 'Freek', 'email' => 'freek@example.com'],
            ]));

        $officient->shouldReceive('getPersonDetail')
            ->times(3)
            ->andReturnUsing(fn (int $personId) => [
                'avatar' => "https://example.com/{$personId}.jpg",
                'employment' => [
                    'first_employment_date' => '2020-01-01',
                    'last_employment_date' => null,
                ],
            ]);

        $officient->shouldReceive('getDayCalendar')
            ->andReturnUsing(fn (int $personId, Carbon $date) => [
                'company_days_off' => [],
                'time_off' => [[
                    'date' => $date->toDateString(),
                    'scheduled_minutes' => $personId === 1 ? 0 : 456,
                    'events' => $personId === 2
                        ? [[
                            'name' => 'Toegestane afwezigheid',
                            'event_type' => 'custom',
                        ]]
                        : [],
                ]],
            ]);

        $this->app->instance(Officient::class, $officient);

        $this->artisan('dashboard:fetch-officient-calendar')->assertSuccessful();

        foreach (OfficientStore::make()->week() as $day) {
            $this->assertSame([
                [
                    'name' => 'Freek',
                    'avatar' => 'https://example.com/3.jpg',
                ],
            ], $day['in_office']);
        }
    }
}
