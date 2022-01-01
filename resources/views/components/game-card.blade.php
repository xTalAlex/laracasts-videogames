<div class="mt-8 game">
    <div class="relative inline-block">
        <a href="{{ route('games.show', $game['slug']) }}">
            @if(isset($game['coverImageUrl']))
            <img src="{{ $game['coverImageUrl'] }}" alt="game cover" class="h-56 transition duration-150 ease-in-out hover:opacity-75">
            @else
            <div class="relative inline-block">
                <div class="h-56 bg-gray-800 w-44"></div>
            </div>
            @endif
        </a>
        @if ($game['rating'])
            <div id="{{ $game['slug'] }}" class="absolute bottom-0 right-0 w-16 h-16 bg-gray-800 rounded-full" style="right: -20px; bottom: -20px">
            </div>

            @push('scripts')
                @include('_rating', [
                    'slug' => $game['slug'],
                    'rating' => $game['rating'],
                    'event' => null,
                ])
            @endpush
        @endif
    </div>
    <a href="{{ route('games.show', $game['slug']) }}" class="block mt-8 text-base font-semibold leading-tight hover:text-gray-400">{{ $game['name'] }}</a>
    <div class="mt-1 text-gray-400">
        {{ $game['platforms'] }}
    </div>
</div>
