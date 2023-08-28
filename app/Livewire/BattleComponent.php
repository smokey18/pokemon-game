<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class BattleComponent extends Component
{
    public $playerPokemon;
    public $opponentPokemon;
    public $playerMoves;
    public $opponentMoves;
    public $selectedMove;
    public $playerHealth = 100;
    public $opponentHealth = 100;
    public $battleLog = [];

    public function mount()
    {
        $this->refreshPokemon();
    }

    public function refreshPokemon()
    {
        $this->battleLog = [];
        $this->playerHealth = $this->opponentHealth = 100;
        $this->selectedMove = null;

        $this->playerPokemon = $this->getPokemon();
        $this->opponentPokemon = $this->getPokemon();

        $this->playerMoves = collect($this->playerPokemon['moves'])->random(3);
        $this->opponentMoves = collect($this->opponentPokemon['moves'])->random(3);
    }

    public function getPokemon()
    {
        $randomKey = rand(1, 800);
        return Cache::remember($randomKey, now()->addHour(), function () use ($randomKey) {
            return Http::get("https://pokeapi.co/api/v2/pokemon/{$randomKey}")->json();
        });
    }

    public function calculateDamage($movePower, $attackerLevel, $attackerStat, $defenderStat, $typeMultiplier)
    {
        $critical = 1;

        $damage = (
            (
                (
                    (2 * $attackerLevel / 5 + 2) *
                    $movePower *
                    $attackerStat / $defenderStat
                ) / 50 + 2
            ) *
            $critical *
            $typeMultiplier *
            rand(217, 255) / 255
        );

        return floor($damage);
    }

    public function attack()
    {
        if (!$this->selectedMove) {
            session()->flash('error', 'Please select a move');
            return;
        }

        $this->battleLog = [];

        $playerMove = $this->playerMoves->firstWhere('move.name', $this->selectedMove);
        if (!$playerMove) {
            session()->flash('error', 'Invalid move');
            return;
        }

        $playerMove = $this->getMoveDetails($this->selectedMove);
        $playerDamage = $this->calculateDamage(
            $playerMove['power'],
            $this->playerHealth,
            $this->playerPokemon['stats'][1]['base_stat'],
            $this->opponentPokemon['stats'][2]['base_stat'],
            1
        );

        $opponentMoveDetails = $this->getMoveDetails(collect($this->opponentPokemon['moves'])->random()['move']['name']);
        $opponentDamage = $this->calculateDamage(
            $opponentMoveDetails['power'],
            $this->opponentHealth,
            $this->opponentPokemon['stats'][1]['base_stat'],
            $this->playerPokemon['stats'][2]['base_stat'],
            1
        );

        $this->playerHealth = max(0, $this->playerHealth - $opponentDamage);
        $this->opponentHealth = max(0, $this->opponentHealth - $playerDamage);

        $this->battleLog[] = "You attacked with {$this->selectedMove} and dealt {$playerDamage} damage.";
        $this->battleLog[] = "Opponent attacked with {$opponentMoveDetails['name']} and dealt {$opponentDamage} damage.";

        if ($this->playerHealth <= 0 || $this->opponentHealth <= 0) {
            $this->battleLog[] = $this->playerHealth <= 0 ? "You fainted. Opponent wins!" : "Opponent fainted. You win!";
        }
    }

    public function getMoveDetails($moveName)
    {
        $response = Http::get("https://pokeapi.co/api/v2/move/{$moveName}");
        return $response->successful() ? $response->json() : null;
    }

    public function render()
    {
        return view('livewire.battle-component');
    }
}
