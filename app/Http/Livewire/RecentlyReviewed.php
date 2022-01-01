<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class RecentlyReviewed extends Component
{
    public $recentlyReviewed = [];

    public function loadRecentlyReviewed()
    {
        $token_file=Storage::disk('local')->get('igdb_access_token.txt');

        $before = Carbon::now()->subMonths(2)->timestamp;
        $current = Carbon::now()->timestamp;

         $recentlyReviewedUnformatted = Http::withHeaders([
            'Client-ID' => config('services.igdb.key'),
            'Authorization' => 'Bearer '. $token_file,
        ])
            ->withBody("
                    fields name, cover.url, first_release_date, total_rating, total_rating_count, platforms.abbreviation, rating, rating_count, summary, slug;
                    where platforms = (130)
                    & (first_release_date >= {$before}
                    & first_release_date < {$current});
                    sort total_rating_count desc;
                    limit 3;
                ","text/plain")
            ->post(config('services.igdb.url').'/games')
            ->json();

        $this->recentlyReviewed = $this->formatForView($recentlyReviewedUnformatted);

        collect($this->recentlyReviewed)->filter(function ($game) {
            return $game['rating'];
        })->each(function ($game) {
            $this->emit('reviewGameWithRatingAdded', [
                'slug' => 'review_'.$game['slug'],
                'rating' => $game['rating'] / 100
            ]);
        });
    }

    public function render()
    {
        return view('livewire.recently-reviewed');
    }

    private function formatForView($games)
    {
        return collect($games)->map(function ($game) {
            return collect($game)->merge([
                'coverImageUrl' => Str::replaceFirst('thumb','cover_big', $game['cover']['url']),
                'rating' => isset($game['rating']) ? round($game['rating']) : null,
                'platforms' => collect($game['platforms'])->pluck('abbreviation')->implode(', '),
            ]);
        })->toArray();
    }
}
