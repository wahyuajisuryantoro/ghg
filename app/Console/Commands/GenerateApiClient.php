<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use App\Models\Api\ApiClients;
use Illuminate\Console\Command;

class GenerateApiClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:generate-client {client_name} {client_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new API client credentials';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $clientName = $this->argument('client_name');
        $clientId = $this->argument('client_id') ?: Str::slug($clientName, '');
        $clientSecretKey = Str::random(16);
        $basicAuthPassword = Str::random(16);
        $clientKey = md5($clientSecretKey);
        $passwordBasicAuth = md5($basicAuthPassword);
        if (ApiClients::where('client_id', $clientId)->exists()) {
            $this->error("Client ID '{$clientId}' sudah ada!");
            return 1;
        }
        $client = ApiClients::create([
            'client_id' => $clientId,
            'client_key' => $clientKey,
            'password_basic_auth' => $passwordBasicAuth,
            'client_name' => $clientName,
            'is_active' => true
        ]);
        $this->info("Client berhasil dibuat:");
        $this->line("Client Name: {$clientName}");
        $this->line("Client ID: {$clientId}");
        $this->line("Client Key: {$clientKey}");
        $this->line("Password Basic Auth: {$passwordBasicAuth}");
        return 0;
    }
}
