<?php

namespace App\Livewire;

use App\Services\DamageCalculatorService;
use App\Services\PokemonService;
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

    // Services
    protected $pokemonService;
    protected $damageService;

    public function __construct()
    {
        $this->pokemonService = new PokemonService();
        $this->damageService = new DamageCalculatorService();
    }

    public function mount()
    {
        $this->refreshPokemon();
    }

    public function refreshPokemon()
    {
        $this->battleLog = [];
        $this->playerHealth = $this->opponentHealth = 100;
        $this->selectedMove = null;

        $this->playerPokemon = $this->pokemonService->getPokemon(rand(1, 800));
        $this->opponentPokemon = $this->pokemonService->getPokemon(rand(1, 800));

        $this->playerMoves = collect($this->playerPokemon['moves'])->random(3);
        $this->opponentMoves = collect($this->opponentPokemon['moves'])->random(3);
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

        $playerMove = $this->pokemonService->getMoveDetails($this->selectedMove);
        $playerDamage = $this->damageService->calculateDamage(
            $playerMove['power'],
            $this->playerHealth,
            $this->playerPokemon['stats'][1]['base_stat'],
            $this->opponentPokemon['stats'][2]['base_stat'],
            1
        );

        $opponentMoveDetails = $this->pokemonService->getMoveDetails(collect($this->opponentPokemon['moves'])->random()['move']['name']);
        $opponentDamage = $this->damageService->calculateDamage(
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

    public function render()
    {
        return view('livewire.battle-component');
    }
}
