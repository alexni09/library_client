<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use App\Services\Misc;

class GetOneBook extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-one-book';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets a single book, which may or may not exist.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $book_count = Redis::get('book_count');
        $min = intval($book_count * 0.5 + 0.5);
        $max = intval($book_count * 1.5);
        $rnd = rand($min, $max);
        $response = Http::acceptJson()->get(env('LIBRARY_API_URL') . '/api/books/' . $rnd);
        $message = 'List Book #' . $rnd . ': returned with status code ' . $response->status() . '.';
        Misc::monitor($this->signature, $message, $response->status());
        $this->info($message);
        return 0;
    }
}