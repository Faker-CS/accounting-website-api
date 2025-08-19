<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCreated;

class TestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test mail functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        try {
            $testData = [
                'name' => 'Test User',
                'email' => $email,
                'password' => 'TestPassword123',
            ];

            Mail::to($email)->send(new UserCreated(
                'emails.usercreated',
                'Test - Welcome to our platform',
                $testData,
                null
            ));

            $this->info("Test email sent successfully to {$email}");
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
            return 1;
        }
    }
}
