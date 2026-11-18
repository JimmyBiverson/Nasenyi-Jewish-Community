<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('nasenyi:admin {--name=} {--email=}')]
#[Description('Create an administrator using a password entered privately at the prompt')]
class CreateAdministrator extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('This command requires an interactive terminal.');

            return self::FAILURE;
        }

        $validator = Validator::make([
            'name' => $this->option('name') ?: $this->ask('Name'),
            'email' => $this->option('email') ?: $this->ask('Email address'),
            'password' => $this->secret('Password (at least 12 characters)', false),
            'password_confirmation' => $this->secret('Confirm password', false),
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', 'max:72', Password::min(12)],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = new User($validator->validated());
        $user->role = 'admin';
        $user->save();
        $this->info('Administrator created. Sign in at /admin/login.');

        return self::SUCCESS;
    }
}
