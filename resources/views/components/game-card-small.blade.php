<div class="flex game">

    <a href="{{ route('games.show', $game['slug']) }}">
        @if(isset($game['coverImageUrl']))
        <img src="{{ $game['coverImageUrl'] }}" alt="game cover" class="w-16 transition duration-150 ease-in-out hover:opacity-75">
        @else
        <div class="flex-none w-16 h-20 bg-gray-800"></div>
        @endif
    </a>
    <div class="ml-4">
        <a href="{{ route('games.show', $game['slug']) }}" class="hover:text-gray-300">{{ $game['name'] }}</a>
        <div class="mt-1 text-sm text-gray-400">{{ $game['releaseDate'] }}</div>
    </div>
</div>
