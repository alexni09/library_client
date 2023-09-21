<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use App\Services\Misc;

class ListCategories extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:list-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists a chunk of available categories, starting randomly.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $category_count = Redis::get('category_count');
        $rnd = rand(1, $category_count);
        $response = Http::get(env('LIBRARY_API_URL') . '/api/categories?start=' . $rnd);
        if ($response->status() !== 200) {
            Misc::monitor($this->signature, 'Failed.', $response->status());
            $this->error('Fetch failed with status code: ' . $response->status() . '.');
            return -1;
        }
        $message = 'Listed ' . count($response->json()['data']) . ' categories.';
        Misc::monitor($this->signature, $message, $response->status());
        $this->info($message);
        return 0;
    }
}