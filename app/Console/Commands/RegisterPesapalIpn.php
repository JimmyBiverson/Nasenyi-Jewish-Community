<?php

namespace App\Console\Commands;

use App\Services\PesapalClient;
use Illuminate\Console\Command;
use RuntimeException;

class RegisterPesapalIpn extends Command
{
    protected $signature = 'pesapal:register-ipn';

    protected $description = 'Register the public donation notification endpoint with Pesapal';

    public function handle(PesapalClient $client): int
    {
        try {
            $id = $client->registerIpn();
        } catch (RuntimeException) {
            $this->error('Could not register the IPN. Check the Pesapal credentials and public HTTPS URL.');

            return self::FAILURE;
        }

        $this->line('Set PESAPAL_NOTIFICATION_ID='.$id);

        return self::SUCCESS;
    }
}
