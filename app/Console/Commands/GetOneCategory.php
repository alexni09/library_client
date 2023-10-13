<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use App\Services\Misc;

class GetOneCategory extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-one-category';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets a single category, which may or may not exist.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $category_count = Redis::get('category_count');
        $min = intval($category_count * 0.5 + 0.5);
        $max = intval($category_count * 1.5);
        $rnd = rand($min, $max);
        $response = Http::get(env('LIBRARY_API_URL') . '/api/categories/' . $rnd);
        $message = 'List Category #' . $rnd . ': returned with status code ' . $response->status() . '.';
        Misc::monitor($this->signature, $message, $response->status());
        $this->info($message);
        return 0;
    }
}