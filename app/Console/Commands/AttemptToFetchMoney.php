<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class AttemptToFetchMoney extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:attempt-to-fetch-money';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A test. Tries to fetch an api endpoint protected by the middleware referer.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $response = Http::get(env('LIBRARY_API_URL') . '/api/money/');
        $this->info('Fetch returned the status code: ' . $response->status() . '.');
        return 0;
    }
}