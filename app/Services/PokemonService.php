<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PokemonService
{
    public function getPokemon($randomKey)
    {
        return Cache::remember($randomKey, now()->addHour(), function () use ($randomKey) {
            return Http::get("https://pokeapi.co/api/v2/pokemon/{$randomKey}")->json();
        });
    }

    public function getMoveDetails($moveName)
    {
        $response = Http::get("https://pokeapi.co/api/v2/move/{$moveName}");
        return $response->successful() ? $response->json() : null;
    }
}
