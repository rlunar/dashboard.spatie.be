<?php

namespace App\Tiles\CookieClub;

use Spatie\Dashboard\Models\Tile;

class CookieClubStore
{
    private Tile $tile;

    public function __construct()
    {
        $this->tile = Tile::firstOrCreateForName('cookieClub');
    }

    public static function make(): self
    {
        return new static();
    }

    /**
     * @param array{
     *     name: string,
     *     imageUrl: string,
     *     submittedBy: string,
     *     score: float,
     *     ratingCount: int,
     * } $cookieOfTheWeek
     * @param array<int, array{
     *     rank: int,
     *     name: string,
     *     submittedBy: string,
     *     score: float,
     *     ratingCount: int,
     * }> $leaderboard
     */
    public function setOverview(array $cookieOfTheWeek, array $leaderboard): self
    {
        $this->tile->putData('overview', [
            'cookieOfTheWeek' => $cookieOfTheWeek,
            'leaderboard' => $leaderboard,
        ]);

        return $this;
    }

    public function cookieOfTheWeek(): ?array
    {
        $cookieOfTheWeek = $this->overview()['cookieOfTheWeek'] ?? null;

        return is_array($cookieOfTheWeek) ? $cookieOfTheWeek : null;
    }

    public function leaderboard(): array
    {
        $leaderboard = $this->overview()['leaderboard'] ?? null;

        return is_array($leaderboard) ? $leaderboard : [];
    }

    private function overview(): array
    {
        $overview = $this->tile->getData('overview');

        return is_array($overview) ? $overview : [];
    }
}
