<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Waterpool;
use App\Jobs\ProcessGetApi;


class FetchApiData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:api';
    protected $description = 'Fetch data from API every 15 minutes';
    /**
     * The console command description.
     *
     * @var string
     */
    /**
     * Execute the console command.
     *
     * @return int
     */
    
     public function handle()
     {
        //dispatch(new ProcessGetApi() );
	app()->call('App\Http\Controllers\DataApiController@index');

     }
}
