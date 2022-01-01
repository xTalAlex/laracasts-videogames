<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GamesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // For Multi Query
        // $client = new \GuzzleHttp\Client(['base_uri' => 'https://api-v3.igdb.com/']);

        // $response = $client->request('POST', 'multiquery', [
        //     'headers' => [
        //         'key' => env('IGDB_KEY'),
        //     ],
        //     'body' => '
        //         query games "Playstation" {
        //             fields name, popularity, platforms.name, first_release_date;
        //             where platforms = {6,48,130,49};
        //             sort popularity desc;
        //             limit 2;
        //         };

        //         query games "Switch" {
        //             fields name, popularity, platforms.name, first_release_date;
        //             where platforms = {6,48,130,49};
        //             sort popularity desc;
        //             limit 6;
        //         };
        //         '
        // ]);

        // $body = $response->getBody();
        // dd(json_decode($body));

        return view('index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $token_file=Storage::disk('local')->get('igdb_access_token.txt');

        $game = Http::withHeaders([
            'Client-ID' => config('services.igdb.key'),
            'Authorization' => 'Bearer '. $token_file,
        ])
            ->withBody("
                    fields name, cover.url, first_release_date, total_rating, total_rating_count, platforms.abbreviation, rating,
                    slug, involved_companies.company.name, genres.name, aggregated_rating, summary, websites.*, videos.*, screenshots.*, similar_games.cover.url, similar_games.name, similar_games.rating,similar_games.platforms.abbreviation, similar_games.slug;
                    where slug=\"{$slug}\";
                ","text/plain"
            )->post(config('services.igdb.url').'/games')
            ->json();

        abort_if(!$game, 404);

        return view('show', [
            'game' => $this->formatGameForView($game[0]),
        ]);
    }

    private function formatGameForView($game)
    {
        return collect($game)->merge([
            'coverImageUrl' =>  array_key_exists('cover', $game) ? Str::replaceFirst('thumb', 'cover_big', $game['cover']['url']) : null,
            'genres' => array_key_exists('genres', $game) ? collect($game['genres'])->pluck('name')->implode(', ') : null,
            'involvedCompanies' => array_key_exists('involved_companies', $game) ? $game['involved_companies'][0]['company']['name'] : null,
            'platforms' => array_key_exists('platforms', $game) ? collect($game['platforms'])->pluck('abbreviation')->implode(', ') : null,
            'memberRating' => array_key_exists('rating', $game) ? round($game['rating']) : '0',
            'criticRating' => array_key_exists('aggregated_rating', $game) ? round($game['aggregated_rating']) : '0',
            'trailer' => array_key_exists('video', $game) ? 'https://youtube.com/embed/'.$game['videos'][0]['video_id'] : null,
            'screenshots' => array_key_exists('screenshots', $game) ? collect($game['screenshots'])->map(function ($screenshot) {
                return [
                    'big' => Str::replaceFirst('thumb', 'screenshot_big', $screenshot['url']),
                    'huge' => Str::replaceFirst('thumb', 'screenshot_huge', $screenshot['url']),
                ];
            })->take(9) : null,
            'similarGames' => array_key_exists('similar_games', $game) ? collect($game['similar_games'])
                ->filter( fn ($game) => 
                    array_key_exists('platforms',$game) && collect($game['platforms'])->pluck('id')->contains(130)
                )
                ->map(function ($game) {
                    return collect($game)
                        ->merge([
                            'coverImageUrl' => array_key_exists('cover', $game)
                                ? Str::replaceFirst('thumb', 'cover_big', $game['cover']['url'])
                                : 'https://via.placeholder.com/264x352',
                            'rating' => isset($game['rating']) ? round($game['rating']) : null,
                            'platforms' => array_key_exists('platforms', $game)
                                ? collect($game['platforms'])->pluck('abbreviation')->implode(', ')
                                : null,
                        ]);
            })->take(6) : null,
            'social' => array_key_exists('platforms', $game) ? [
                'website' => collect($game['websites'])->first(),
                'facebook' => collect($game['websites'])->filter(function ($website) {
                    return Str::contains($website['url'], 'facebook');
                })->first(),
                'twitter' => collect($game['websites'])->filter(function ($website) {
                    return Str::contains($website['url'], 'twitter');
                })->first(),
                'instagram' => collect($game['websites'])->filter(function ($website) {
                    return Str::contains($website['url'], 'instagram');
                })->first(),
            ] : null,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
