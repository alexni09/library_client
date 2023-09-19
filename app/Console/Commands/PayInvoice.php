<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\Misc;
use App\Models\User;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class PayInvoice extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pay-invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Registered user locates and pays an invoice.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $invoice = Invoice::first();
        if (!$invoice) {
            Misc::monitor($this->signature, 'Pay invoice failed. None found!', 404);
            $this->error('Pay invoice failed. None found! (404)');
            return -1;
        }
        $user = User::find($invoice->user_id);
        if (!$user) {
            Misc::monitor($this->signature, 'Pay invoice failed. User not found!', 422);
            $this->error('Pay invoice failed. User not found! (422)');
            return -1;
        }
        $response1 = Http::post(env('LIBRARY_API_URL') . '/api/auth/login', [
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
        $response2 = Http::withToken($access_token)->get(env('LIBRARY_API_URL') . '/api/list-balance-due-open/');
        if ($response2->status() === 204) DB::table('invoices')->where('user_id', $user->id)->delete();
        if ($response2->status() !== 200) {
            Misc::monitor($this->signature, 'Listing open values failed.', $response2->status());
            $this->error('Listing open values failed with status code: ' . $response2->status() . '.');
            return -1;
        }
        $exemplar_id = intval($response2->json()['data'][0]['exemplar_id']);
        $due_value = intval($response2->json()['data'][0]['due_value']);
        $payment_id = intval($response2->json()['data'][0]['id']);
        Misc::monitor($this->signature, 'Successfully located the payment to be made.', $response2->status());
        $response3 = Http::withToken($access_token)->patch(env('LIBRARY_API_URL') . '/api/pay/' . $payment_id, [
            'money' => $due_value
        ]);
        if ($response3->status() !== 200) {
            Misc::monitor($this->signature, 'The actual payment failed.', $response3->status());
            $this->error('The actual payment failed with status code: ' . $response3->status() . '.');
            return -1;
        }
        $invoice2 = Invoice::where('user_id', $user->id)->where('exemplar_id', $exemplar_id)->first();
        if (isset($invoice2)) $invoice2->delete();
        Misc::monitor($this->signature, 'Successfully paid for borrowing exemplar #' . $exemplar_id . '.', $response3->status());
        $response4 = Http::withToken($access_token)->post(env('LIBRARY_API_URL') . '/api/auth/logout/');
        if ($response4->status() !== 204) {
            Misc::monitor($this->signature, 'Logout failed.', $response4->status());
            $this->error('Logout failed with status code: ' . $response4->status() . '.');
            return -1;
        }
        Misc::monitor($this->signature, 'Successfully logged out.', $response4->status());
        $this->info('Successfully found an invoice to be paid; payed it; and then logged out.');
        return 0;
    }
}