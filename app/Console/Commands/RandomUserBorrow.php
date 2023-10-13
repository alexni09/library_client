<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use App\Services\Misc;
use App\Models\User;
use App\Models\Borrowing;

class RandomUserBorrow extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:random-user-borrow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'An already registered user logs in and borrows zero or more exemplars.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $user = User::orderByRaw('rand()')->first();
        if (!$user) {
            Misc::monitor($this->signature, 'Borrowing failed. User not found!', 404);
            $this->error('Borrowing failed. User not found! (404)');
            return -1;
        }
        $response1 = Http::acceptJson()->post(env('LIBRARY_API_URL') . '/api/auth/login', [
            'email' => $user->email,
            'password' => env('DEFAULT_USER_PASSWORD', '12345678')
        ]);
        if ($response1->status() !== 201) {
            Misc::monitor($this->signature, 'Login failed for user #' . $user->id . '.', $response1->status());
            $this->error('Login failed for user #' . $user->id . '. Status code: ' . $response1->status() . '.');
            return -1;
        }
        $access_token = $response1->json('access_token');
        Misc::monitor($this->signature, 'Successfully logged in user #' . $user->id . '.', $response1->status());
        $n = rand(1,4);
        for ($i=0; $i<$n; $i++) {
            $exemplar_id = rand(1, intval(Redis::get('exemplar_count')));
            $response2 = Http::acceptJson()->withToken($access_token)->post(env('LIBRARY_API_URL') . '/api/borrow/' . $exemplar_id);
            if ($response2->status() !== 201) {
                Misc::monitor($this->signature, 'Borrowing failed.', $response2->status());
                $this->error('Borrowing failed with status code: ' . $response2->status() . '.');
                return -1;
            }
            Borrowing::create([
                'user_id' => $user->id,
                'exemplar_id' => $exemplar_id
            ]);
            Misc::monitor($this->signature, 'Successfully borrowed exemplar #' . $exemplar_id . '.', $response2->status());
        }
        $response3 = Http::acceptJson()->withToken($access_token)->post(env('LIBRARY_API_URL') . '/api/auth/logout/');
        if ($response3->status() !== 204) {
            Misc::monitor($this->signature, 'Logout failed.', $response3->status());
            $this->error('Logout failed with status code: ' . $response3->status() . '.');
            return -1;
        }
        Misc::monitor($this->signature, 'Successfully logged out.', $response3->status());
        $this->info('Successfully logged in an existing user; who borrowed some exemplars and then logged out.');
        return 0;
    }
}