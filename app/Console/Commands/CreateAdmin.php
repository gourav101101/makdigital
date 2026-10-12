<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email?} {--name=MAK Administrator} {--local : Generate private credentials for this local workspace}';

    protected $description = 'Create an administrator using a password entered privately';

    public function handle(): int
    {
        if ($this->option('local')) {
            if (! app()->environment('local')) {
                $this->error('Generated workspace access is only available in the local environment.');

                return self::FAILURE;
            }
            $email = $this->argument('email') ?: 'admin@makdigital.local';
            if (! filter_var($email, FILTER_VALIDATE_EMAIL) || User::where('email', $email)->exists()) {
                $this->error('Use a new valid email address. Existing credentials were not changed.');

                return self::FAILURE;
            }
            $password = Str::random(28);
            $user = new User;
            $user->name = $this->option('name');
            $user->email = $email;
            $user->password = Hash::make($password);
            $user->is_admin = true;
            $user->save();
            Storage::disk('local')->put('admin-access.json', json_encode(['url' => url('/admin/login'), 'email' => $email, 'password' => $password], JSON_PRETTY_PRINT));
            $this->info('Local administrator created. Credentials saved privately in storage/app/private/admin-access.json.');

            return self::SUCCESS;
        }
        $email = $this->argument('email') ?: $this->ask('Administrator email');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('A valid email address is required.');

            return self::FAILURE;
        }
        if (User::where('email', $email)->exists()) {
            $this->error('This email already exists. No account was changed.');

            return self::FAILURE;
        }
        $password = $this->secret('Password (at least 12 characters)');
        $confirmation = $this->secret('Confirm password');
        $validator = Validator::make(['password' => $password, 'password_confirmation' => $confirmation], ['password' => 'required|string|min:12|confirmed']);
        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }
        $user = new User;
        $user->name = $this->option('name');
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->is_admin = true;
        $user->save();
        $this->info('Administrator created. Sign in at /admin/login.');

        return self::SUCCESS;
    }
}
