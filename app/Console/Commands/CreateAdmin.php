<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the admin user for the central app.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('Name');
        $email = $this->ask('Email Address');
        $password = $this->secret('Password');

        if (Admin::firstWhere('email', $email)) {
            $this->alert("Admin user '{$email}' already exists.");

            return;
        }

        Admin::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $this->info("Admin user '{$email}' created successfully.");
    }
}
