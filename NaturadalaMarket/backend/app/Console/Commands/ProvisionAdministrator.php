<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ProvisionAdministrator extends Command
{
    protected $signature = 'marketplace:provision-admin {email : Email address for the initial administrator}';

    protected $description = 'Provision an administrator account through a local one-time CLI flow';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));
        $validator = Validator::make(['email' => $email], [
            'email' => ['required', 'email:rfc', 'max:190', 'unique:users,email'],
        ]);

        if ($validator->fails()) {
            $this->components->error($validator->errors()->first('email'));

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Administrator name'));
        if ($name === '' || mb_strlen($name) > 190) {
            $this->components->error('Enter an administrator name of 1–190 characters.');

            return self::FAILURE;
        }

        $password = $this->secret('Administrator password (12+ characters)');
        $confirmation = $this->secret('Confirm administrator password');

        if (
            ! is_string($password) ||
            ! hash_equals($password, (string) $confirmation) ||
            Validator::make(['password' => $password], [
                'password' => ['required', Password::min(12)->mixedCase()->numbers()->symbols()],
            ])->fails()
        ) {
            $this->components->error('Password confirmation failed or the password does not meet the strength requirements.');

            return self::FAILURE;
        }

        if (! $this->confirm("Create verified administrator account for {$email}?")) {
            $this->components->warn('Administrator provisioning cancelled.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($email, $name, $password): void {
            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'status' => 'active',
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();

            $role = Role::query()->where('slug', 'admin')->firstOrFail();
            $user->roles()->attach($role);

            SecurityEvent::query()->create([
                'user_id' => $user->id,
                'event_type' => 'admin_account_provisioned',
            ]);
        });

        $this->components->info('Administrator created. Sign in locally, configure MFA, and keep the password in your password manager.');
        $this->components->warn('The admin dashboard stays blocked until authenticator-app MFA is enabled.');

        return self::SUCCESS;
    }
}
