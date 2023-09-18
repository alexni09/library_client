<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use App\Services\Misc;

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
            Misc::monitor($this->signature, 'Failed.', $response->status());
            $this->error('Fetch failed with status code: ' . $response->status() . '.');
            return -1;
        }
        $message = 'Returned ' . count($response->json()['data']) . ' books from category #' . $rnd . '.';
        Misc::monitor($this->signature, $message, $response->status());
        $this->info($message);
        return 0;
    }
}