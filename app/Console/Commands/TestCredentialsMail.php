<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\CredentialsMail;
use App\Models\User;

class TestCredentialsMail extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mail:test-credentials {email}';

    /**
     * The console command description.
     */
    protected $description = 'Test CredentialsMail for password reset';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        try {
            // Create a test user object
            $testUser = new User();
            $testUser->name = 'Test User';
            $testUser->email = $email;
            
            $newPassword = 'ResetPassword123';

            Mail::to($email)->send(new CredentialsMail($testUser, $newPassword));
            
            $this->info("✅ CredentialsMail sent successfully to {$email}");
            $this->line("   Password: {$newPassword}");
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Failed to send CredentialsMail: " . $e->getMessage());
            return 1;
        }
    }
}
