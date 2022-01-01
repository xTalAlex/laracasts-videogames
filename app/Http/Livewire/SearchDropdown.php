<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SearchDropdown extends Component
{
    public $search = '';
    public $searchResults = [];

    public function render()
    {
        if (strlen($this->search) >= 2) {

            $token_file=Storage::disk('local')->get('igdb_access_token.txt');

            $this->searchResults =  Http::withHeaders([
                'Client-ID' => config('services.igdb.key'),
                'Authorization' => 'Bearer '. $token_file,
            ])
                ->withBody("
                        search \"{$this->search}\";
                        fields name, slug, cover.url;
                        limit 8;
                ","text/plain")
                ->post(config('services.igdb.url').'/games')
                ->json();
        }

        return view('livewire.search-dropdown');
    }
}
