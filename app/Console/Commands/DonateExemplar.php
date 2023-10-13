<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use App\Services\Misc;
use App\Models\User;

class DonateExemplar extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:donate-exemplar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Random user donates an exemplar of a random book.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $user = User::orderByRaw('rand()')->first();
        if (!$user) {
            Misc::monitor($this->signature, 'Donation failed. User not found!', 404);
            $this->error('Donation failed. User not found! (404)');
            return -1;
        }
        $book_count = Redis::get('book_count');
        $book_id = rand(1, $book_count);
        $response1 = Http::acceptJson()->get(env('LIBRARY_API_URL') . '/api/books/' . $book_id );
        if ($response1->status() !== 200) {
            Misc::monitor($this->signature, 'Failed.', $response->status());
            $this->error('Fetch failed with status code: ' . $response->status() . '.');
            return -1;
        }
        $response2 = Http::acceptJson()->post(env('LIBRARY_API_URL') . '/api/auth/login', [
            'email' => $user->email,
            'password' => env('DEFAULT_USER_PASSWORD', '12345678')
        ]);
        if ($response2->status() !== 201) {
            Misc::monitor($this->signature, 'Login failed for user #' . $user->id . '.', $response2->status());
            $this->error('Login failed for user #' . $user->id . '. Status code: ' . $response2->status() . '.');
            return -1;
        }
        $access_token = $response2->json('access_token');
        Misc::monitor($this->signature, 'Successfully logged in user #' . $user->id . '.', $response2->status());
        $condition = rand(1,4);
        $response3 = Http::acceptJson()->withToken($access_token)->post(env('LIBRARY_API_URL') . '/api/exemplars/donate', [
            'book_id' => $book_id,
            'condition' => $condition        
        ]);
        if ($response3->status() !== 201) {
            Misc::monitor($this->signature, 'Donation failed.', $response3->status());
            $this->error('Donation failed with status code: ' . $response3->status() . '.');
            return -1;
        }
        Misc::monitor($this->signature, 'Successfully donated exemplar of book #' . $book_id . ', condition #' . $condition . '.', $response3->status());
        $response4 = Http::acceptJson()->withToken($access_token)->post(env('LIBRARY_API_URL') . '/api/auth/logout/');
        if ($response4->status() !== 204) {
            Misc::monitor($this->signature, 'Logout failed.', $response4->status());
            $this->error('Logout failed with status code: ' . $response4->status() . '.');
            return -1;
        }
        Misc::monitor($this->signature, 'Successfully logged out.', $response4->status());
        $this->info('Successfully found an existing user; donated an exemplar; and then logged out.');
        return 0;
    }
}