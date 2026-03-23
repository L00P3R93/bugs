<?php

namespace App\Jobs;

use App\Facades\KadiApi;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessVerifiedUser implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $user) {}

    public function handle(): void
    {
        // Pull the plain password cached at registration (one-time read + delete)
        $plainPassword = Cache::pull("user.plain_password.{$this->user->id}");

        $this->registerWithKadiApi();
        $this->insertIntoKadiDatabase($plainPassword);
        $this->sendWelcomeEmail($plainPassword);
    }

    /**
     * POST to KadiApi /customers and store the returned customer_id as linked_id.
     */
    private function registerWithKadiApi(): void
    {
        try {
            $response = KadiApi::createCustomer([
                'google_id' => $this->user->account_no,
                'account_no' => $this->user->account_no,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'id_no' => (string) $this->user->id,
                'phone_no' => $this->user->phone,
            ]);

            if (isset($response['customer_id'])) {
                $this->user->update(['linked_id' => $response['customer_id']]);
            }
        } catch (RequestException|ConnectionException $e) {
            Log::error('KadiApi registration failed for user '.$this->user->id.': '.$e->getMessage());
        }
    }

    /**
     * Insert a new account record into the kadi database.
     */
    private function insertIntoKadiDatabase(?string $plainPassword): void
    {
        try {
            DB::connection('kadi')->table('accounts')->insert([
                'name' => $this->user->name,
                'phone' => $this->user->phone,
                'email' => $this->user->email,
                'password' => $plainPassword,
            ]);
        } catch (\Throwable $e) {
            Log::error('Kadi DB insert failed for user '.$this->user->id.': '.$e->getMessage());
        }
    }

    private function sendWelcomeEmail(?string $plainPassword): void
    {
        Mail::to($this->user->email)->send(new WelcomeEmail($this->user, $plainPassword));
    }
}
