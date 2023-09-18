<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;

class FetchCount extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch the count of categories, books and exemplars from the api.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $response = Http::get(env('LIBRARY_API_URL') . '/api/count/');
        if ($response->status() !== 200) {
            $this->error('Fetch failed with status code: ' . $response->data() . '.');
            return -1;
        }
        Redis::set('category_count', $response->json()['data']['category_count']);
        Redis::set('book_count', $response->json()['data']['book_count']);
        Redis::set('exemplar_count', $response->json()['data']['exemplar_count']);
        $this->info('Success!');
        return 0;
    }
}