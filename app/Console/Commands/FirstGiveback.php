<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\Misc;
use App\Models\User;
use App\Models\Borrowing;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class FirstGiveback extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:first-giveback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Registered user gives back an exemplar.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $borrowing = Borrowing::first();
        if (!$borrowing) {
            Misc::monitor($this->signature, 'Giveback failed. None found!', 404);
            $this->error('Giveback failed. None found! (404)');
            return -1;
        }
        $user = User::find($borrowing->user_id);
        if (!$user) {
            Misc::monitor($this->signature, 'Giveback failed. User not found!', 422);
            $this->error('Giveback failed. User not found! (422)');
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
        $exemplar_id = $borrowing->exemplar_id;
        $response2 = Http::acceptJson()->withToken($access_token)->patch(env('LIBRARY_API_URL') . '/api/giveback/' . $exemplar_id);
        if ($response2->status() !== 200) {
            Misc::monitor($this->signature, 'Giveback failed.', $response2->status());
            $this->error('Giveback failed with status code: ' . $response2->status() . '.');
            return -1;
        }
        DB::beginTransaction();
        $borrowing->delete();
        Invoice::create([
            'user_id' => $user->id,
            'exemplar_id' => $exemplar_id
        ]);
        DB::commit();
        Misc::monitor($this->signature, 'Successfully returned exemplar #' . $exemplar_id . '.', $response2->status());
        $response3 = Http::acceptJson()->withToken($access_token)->post(env('LIBRARY_API_URL') . '/api/auth/logout/');
        if ($response3->status() !== 204) {
            Misc::monitor($this->signature, 'Logout failed.', $response3->status());
            $this->error('Logout failed with status code: ' . $response3->status() . '.');
            return -1;
        }
        Misc::monitor($this->signature, 'Successfully logged out.', $response3->status());
        $this->info('Successfully found a user which has an exemplar borrowed; returned that exemplar and then logged out.');
        return 0;
    }
}