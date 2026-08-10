<?php

namespace App\Tiles\CookieClub\Commands;

use Throwable;
use Illuminate\Console\Command;
use App\Tiles\CookieClub\CookieClubStore;
use Illuminate\Support\Facades\Http;

class FetchCookieClubOverviewCommand extends Command
{
    protected $signature = 'dashboard:fetch-cookie-club-overview';

    protected $description = 'Fetch the Cookie Club cookie of the week and leaderboard';

    public function handle(): int
    {
        $this->info('Fetching Cookie Club overview...');

        try {
            $response = Http::acceptJson()
                ->get(config('services.cookie_club.overview_url'));

            if (! $response->successful()) {
                $this->error('The Cookie Club API request failed.');

                return self::FAILURE;
            }

            $payload = $response->json();
        } catch (Throwable) {
            $this->error('The Cookie Club API request failed.');

            return self::FAILURE;
        }

        $overview = is_array($payload) ? $this->normalizeOverview($payload) : null;

        if ($overview === null) {
            $this->error('The Cookie Club API returned invalid data.');

            return self::FAILURE;
        }

        CookieClubStore::make()->setOverview(
            $overview['cookieOfTheWeek'],
            $overview['leaderboard'],
        );

        $this->comment('Cookie Club overview updated.');

        return self::SUCCESS;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{
     *     cookieOfTheWeek: array{
     *         name: string,
     *         imageUrl: string,
     *         submittedBy: string,
     *         score: float,
     *         ratingCount: int,
     *     },
     *     leaderboard: array<int, array{
     *         rank: int,
     *         name: string,
     *         submittedBy: string,
     *         score: float,
     *         ratingCount: int,
     *     }>,
     * }|null
     */
    private function normalizeOverview(array $payload): ?array
    {
        $cookieOfTheWeek = $this->normalizeCookie($payload['cookie_of_the_week'] ?? null);
        $leaderboard = $payload['leaderboard'] ?? null;

        if ($cookieOfTheWeek === null) {
            return null;
        }

        if (! is_array($leaderboard) || ! array_is_list($leaderboard)) {
            return null;
        }

        $normalizedLeaderboard = [];

        foreach ($leaderboard as $cookie) {
            $normalizedCookie = $this->normalizeLeaderboardCookie($cookie);

            if ($normalizedCookie === null) {
                return null;
            }

            $normalizedLeaderboard[] = $normalizedCookie;
        }

        return [
            'cookieOfTheWeek' => $cookieOfTheWeek,
            'leaderboard' => $normalizedLeaderboard,
        ];
    }

    private function normalizeCookie(mixed $cookie): ?array
    {
        if (! is_array($cookie)) {
            return null;
        }

        $name = $cookie['name'] ?? null;
        $imageUrl = $cookie['image_url'] ?? null;
        $submittedBy = $cookie['submitted_by'] ?? null;
        $score = $cookie['score'] ?? null;
        $ratingCount = $cookie['rating_count'] ?? null;

        if (! is_string($name) || trim($name) === '') {
            return null;
        }

        if (! is_string($imageUrl) || ! str_starts_with($imageUrl, 'https://')) {
            return null;
        }

        if (! is_string($submittedBy) || trim($submittedBy) === '') {
            return null;
        }

        if (! is_numeric($score) || ! is_int($ratingCount)) {
            return null;
        }

        return [
            'name' => $name,
            'imageUrl' => $imageUrl,
            'submittedBy' => $submittedBy,
            'score' => (float) $score,
            'ratingCount' => $ratingCount,
        ];
    }

    private function normalizeLeaderboardCookie(mixed $cookie): ?array
    {
        if (! is_array($cookie)) {
            return null;
        }

        $rank = $cookie['rank'] ?? null;
        $name = $cookie['name'] ?? null;
        $submittedBy = $cookie['submitted_by'] ?? null;
        $score = $cookie['score'] ?? null;
        $ratingCount = $cookie['rating_count'] ?? null;

        if (! is_int($rank) || $rank < 1) {
            return null;
        }

        if (! is_string($name) || trim($name) === '') {
            return null;
        }

        if (! is_string($submittedBy) || trim($submittedBy) === '') {
            return null;
        }

        if (! is_numeric($score) || ! is_int($ratingCount)) {
            return null;
        }

        return [
            'rank' => $rank,
            'name' => $name,
            'submittedBy' => $submittedBy,
            'score' => (float) $score,
            'ratingCount' => $ratingCount,
        ];
    }
}
