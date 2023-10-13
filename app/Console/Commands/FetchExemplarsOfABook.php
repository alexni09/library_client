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
    protected $description = 'Fetches exemplars of a random book, given the condition parameter.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $book_count = Redis::get('book_count');
        $rnd = rand(1, $book_count);
        $condition = rand(1,4);
        $response = Http::acceptJson()->get(env('LIBRARY_API_URL') . '/api/exemplars/list/' . $rnd, '?condition=' . $condition );
        if ($response->status() === 204) {
            Misc::monitor($this->signature, 'No borrowable exemplars for book #' . $rnd .  ', condition #' . $condition . '.', $response->status());
            $this->error('No borrowable exemplars for book #' . $rnd .  ', condition #' . $condition . '. Status code: ' . $response->status() . '.');
            return 0;
        } else if ($response->status() !== 200) {
            Misc::monitor($this->signature, 'Failed.', $response->status());
            $this->error('Fetch failed with status code: ' . $response->status() . '.');
            return -1;
        }
        $count = count($response->json()['data']);
        $s = $count === 1 ? '' : 's';
        $message = 'Fetched ' . $count . ' exemplar' . $s . ' from book #' . $rnd . ', condition #' . $condition  . '.';
        Misc::monitor($this->signature, $message, $response->status());
        $this->info($message);
        return 0;
    }
}