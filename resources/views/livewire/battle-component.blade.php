<div>
    <div class="bg-gray-100 min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md max-w-xl w-full">
            <h1 class="text-2xl font-bold mb-5 text-center">Pokemon Battle</h1>

            <div class="flex mb-4">
                <div class="w-1/2 pr-2">
                    <h2 class="text-lg font-semibold">
                        You: {{ ucwords(str_replace('-', ' ', $playerPokemon['name'])) }}
                    </h2>
                    <p class="mb-2">Health: {{ $playerHealth }}/100</p>
                    <select class="w-full mt-2 p-2 border rounded" wire:model="selectedMove">
                        <option value="" selected>Select Move</option>
                        @foreach ($playerMoves as $move)
                        <option value="{{ $move['move']['name'] }}">
                            {{ ucwords(str_replace('-', ' ', $move['move']['name'])) }}
                        </option>
                        @endforeach
                    </select>

                    @if(session()->has('success'))
                    <div class="text-green-500 mt-1 mb-1">
                        {{ session()->get('success') }}
                    </div>
                    @endif
                    @if(session()->has('error'))
                    <div class="text-red-500 mt-1 mb-1">
                        {{ session()->get('error') }}
                    </div>
                    @endif

                    @if ($playerHealth > 0 && $opponentHealth > 0)
                    <button class="mt-2 bg-blue-500 text-white px-4 py-2 rounded" wire:click="attack">Attack</button>
                    @endif
                </div>

                <div class="w-1/2 pl-2">
                    <div class="mb-4">
                        <h2 class="text-lg font-semibold">
                            Opponent: {{ ucwords(str_replace('-', ' ', $opponentPokemon['name'])) }}
                        </h2>
                        <p class="mb-2">Health: {{ $opponentHealth }}/100</p>
                    </div>
                </div>
            </div>

            @if (count($battleLog) > 0)
            <div class="text-center mt-4">
                <h2 class="text-lg font-semibold">Damage Done:</h2>
                <ul>
                    @foreach ($battleLog as $log)
                    <li>{{ $log }}</li>
                    @endforeach
                </ul>

                @if ($playerHealth <= 0 || $opponentHealth <=0)
                    <button class="mt-4 bg-blue-500 text-white px-4 py-2 rounded" wire:click="refreshPokemon">
                        Reload
                    </button>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
