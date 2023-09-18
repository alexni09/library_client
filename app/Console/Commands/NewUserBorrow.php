<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use App\Services\Misc;
use App\Models\User;
use App\Models\Borrowing;

class NewUserBorrow extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:new-user-borrow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A new user registers itself and borrows an exemplar.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $user = User::factory()->create();
        $response1 = Http::post(env('LIBRARY_API_URL') . '/api/auth/register', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => env('DEFAULT_USER_PASSWORD', '12345678'),
            'password_confirmation' => env('DEFAULT_USER_PASSWORD', '12345678')
        ]);
        if ($response1->status() !== 201) {
            Misc::monitor($this->signature, 'Registration failed.', $response1->status());
            $this->error('Registration failed with status code: ' . $response1->status() . '.');
            return -1;
        }
        $access_token = $response1->json('access_token');
        Misc::monitor($this->signature, 'Successfully registered new user #' . $user->id . '.', $response1->status());
        $exemplar_id = rand(1, intval(Redis::get('exemplar_count')));
        $response2 = Http::withToken($access_token)->post(env('LIBRARY_API_URL') . '/api/borrow/' . $exemplar_id);
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
        $response3 = Http::withToken($access_token)->post(env('LIBRARY_API_URL') . '/api/auth/logout/');
        if ($response3->status() !== 204) {
            Misc::monitor($this->signature, 'Logout failed.', $response3->status());
            $this->error('Logout failed with status code: ' . $response3->status() . '.');
            return -1;
        }
        Misc::monitor($this->signature, 'Successfully logged out.', $response3->status());
        $this->info('Successfully created a new user; who borrowed an exemplar and then logged out.');
        return 0;
    }
}