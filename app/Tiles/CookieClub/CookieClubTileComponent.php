<?php

namespace App\Tiles\CookieClub;

use Illuminate\Contracts\View\View;
use Spatie\Dashboard\Components\BaseTileComponent;

class CookieClubTileComponent extends BaseTileComponent
{
    public function render(): View
    {
        $store = CookieClubStore::make();

        return view('components.tiles.cookieClub', [
            'cookieOfTheWeek' => $store->cookieOfTheWeek(),
        ]);
    }
}
