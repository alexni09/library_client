<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use App\Services\Misc;

class GetOneExemplar extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-one-exemplar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets a single exemplar, which may or may not exist.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $exemplar_count = Redis::get('exemplar_count');
        $min = intval($exemplar_count * 0.5 + 0.5);
        $max = intval($exemplar_count * 1.5);
        $rnd = rand($min, $max);
        $response = Http::acceptJson()->get(env('LIBRARY_API_URL') . '/api/exemplars/' . $rnd);
        $message = 'List Exemplar #' . $rnd . ': returned with status code ' . $response->status() . '.';
        Misc::monitor($this->signature, $message, $response->status());
        $this->info($message);
        return 0;
    }
}