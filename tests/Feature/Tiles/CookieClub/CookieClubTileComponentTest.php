<?php

namespace Tests\Feature\Tiles\CookieClub;

use Tests\TestCase;
use Livewire\Livewire;
use App\Tiles\CookieClub\CookieClubStore;
use App\Tiles\CookieClub\CookieClubTileComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CookieClubTileComponentTest extends TestCase
{
    use RefreshDatabase;

    public function testItRendersAnUnavailableStateBeforeTheFirstFetch(): void
    {
        Livewire::test(CookieClubTileComponent::class, ['position' => 'b1:c6'])
            ->assertSee('Cookie data unavailable')
            ->assertSee('wire:poll.60s', false);
    }

    public function testItRendersTheCookieOfTheWeekWithoutTheLeaderboard(): void
    {
        CookieClubStore::make()->setOverview([
            'name' => 'Chocolate chip',
            'imageUrl' => 'https://cookie-club.spatie.be/images/chocolate-chip.webp',
            'submittedBy' => 'Alex',
            'score' => 4.25,
            'ratingCount' => 12,
        ], collect(range(1, 6))
            ->map(fn (int $rank): array => [
                'rank' => $rank,
                'name' => "Cookie {$rank}",
                'submittedBy' => "Baker {$rank}",
                'score' => 5 - ($rank / 10),
                'ratingCount' => 10,
            ])
            ->all());

        Livewire::test(CookieClubTileComponent::class, ['position' => 'b1:c6'])
            ->assertSee('Chocolate chip')
            ->assertSee('https://cookie-club.spatie.be/images/chocolate-chip.webp', false)
            ->assertSee('Brought by Alex')
            ->assertDontSee('12 ratings')
            ->assertSee('4.25')
            ->assertDontSee('Highest rated')
            ->assertDontSee('Cookie 1');
    }
}
