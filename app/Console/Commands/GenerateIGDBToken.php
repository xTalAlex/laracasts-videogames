<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GenerateIGDBToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'igdbtoken:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate IGDB Token for the application.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /*
            POST https://id.twitch.tv/oauth2/token
                ?client_id=retgzhvpsxjwun0rvrb1rfwheegu1yw
                &client_secret=dfgh4h5iug3iug35g97131dgh3947u4
                &grant_type=client_credentials
        */

        $url="https://id.twitch.tv/oauth2/token?client_id=".config('services.igdb.key')."&client_secret=".config('services.igdb.secret')."&grant_type=client_credentials";
        $response = Http::post($url)->json();

        if($response['access_token'] ?? false){
            dump($response['access_token']);
            Storage::disk('local')->put('igdb_access_token.txt',$response['access_token']);
        }

        return 0;
    }
}
