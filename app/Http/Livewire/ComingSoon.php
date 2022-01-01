<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ComingSoon extends Component
{
    public $comingSoon = [];

    public function loadComingSoon()
    {
        $token_file=Storage::disk('local')->get('igdb_access_token.txt');
        if ($token_file) {
            $current = Carbon::now()->timestamp;

            $comingSoonUnformatted = Http::withHeaders([
                    'Client-ID' => config('services.igdb.key'),
                    'Authorization' => 'Bearer '. $token_file,
                ])
                ->withBody("fields name, cover.url, total_rating_count, first_release_date, platforms.abbreviation, rating, rating_count, summary, slug;
                    where platforms = (130)
                    & (first_release_date >= {$current});
                    sort first_release_date asc;
                    limit 4;", "text/plain")
                ->post(config('services.igdb.url').'/games')
                ->json();

            $this->comingSoon = $this->formatForView($comingSoonUnformatted);
        }
    }

    public function render()
    {
        return view('livewire.coming-soon');
    }

    private function formatForView($games)
    {

        return collect($games)->map(function ($game) {
            return collect($game)->merge([
                'coverImageUrl' => array_key_exists('cover', $game) ? Str::replaceFirst('thumb','cover_small', $game['cover']['url']) : null,
                'releaseDate' => Carbon::parse($game['first_release_date'])->format('M d, Y'),
            ]);
        })->toArray();
    }
}
