<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;

class FetchRandomBooks extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-random-books';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches all books from a random category.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $category_count = Redis::get('category_count');
        $rnd = rand(1, $category_count);
        $response = Http::get(env('LIBRARY_API_URL') . '/api/books-by-category/' . $rnd);
        if ($response->status() !== 200) {
            $this->error('Fetch failed with status code: ' . $response->data() . '.');
            return -1;
        }
        $this->info('Returned ' . count($response->json()['data']) . ' books in category #' . $rnd . '.');
        return 0;
    }
}