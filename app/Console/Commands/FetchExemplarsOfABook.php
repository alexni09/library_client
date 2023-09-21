<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use App\Services\Misc;

class FetchExemplarsOfABook extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-exemplars-of-a-book';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches exemplars of a random book, given the parameters.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $book_count = Redis::get('book_count');
        $rnd = rand(1, $book_count);
        $response = Http::get(env('LIBRARY_API_URL') . '/api/exemplars/list/' . $rnd, '?condition=' . rand(2,4) );
        if ($response->status() !== 200) {
            Misc::monitor($this->signature, 'Failed.', $response->status());
            $this->error('Fetch failed with status code: ' . $response->status() . '.');
            return -1;
        }
        $message = 'Fetched ' . count($response->json()['data']) . ' exemplars from book #' . $rnd . '.';
        Misc::monitor($this->signature, $message, $response->status());
        $this->info($message);
        return 0;
    }
}