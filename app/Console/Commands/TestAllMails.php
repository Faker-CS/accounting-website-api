<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\UserCreated;
use App\Mail\CompanyCreatedMail;
use App\Mail\CredentialsMail;
use App\Mail\SendCompanyFileMail;

class TestAllMails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test-all {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all mail functionalities';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $errors = [];
        $successes = [];

        // Test User Created Mail
        $this->info('Testing User Created Mail...');
        try {
            $userData = [
                'name' => 'Test User',
                'email' => $email,
                'password' => 'TestPassword123',
            ];

            Mail::to($email)->send(new UserCreated(
                'emails.usercreated',
                'Test - Bienvenue sur notre plateforme',
                $userData,
                null
            ));
            $successes[] = 'User Created Mail';
        } catch (\Exception $e) {
            $errors[] = 'User Created Mail: ' . $e->getMessage();
        }

        // Test Company Created Mail
        $this->info('Testing Company Created Mail...');
        try {
            $companyData = [
                'name' => 'Test Company Owner',
                'email' => $email,
                'password' => 'CompanyPassword123',
            ];

            Mail::to($email)->send(new CompanyCreatedMail(
                'emails.companycreated',
                'Test - Compte entreprise prêt',
                $companyData,
                null
            ));
            $successes[] = 'Company Created Mail';
        } catch (\Exception $e) {
            $errors[] = 'Company Created Mail: ' . $e->getMessage();
        }

        // Test Forgot Password Mail (using CredentialsMail)
        $this->info('Testing Forgot Password Mail...');
        try {
            // Create a test user object
            $testUser = new \App\Models\User();
            $testUser->name = 'Test User';
            $testUser->email = $email;
            
            $newPassword = 'NewPassword123';

            Mail::to($email)->send(new CredentialsMail($testUser, $newPassword));
            $successes[] = 'Forgot Password Mail (CredentialsMail)';
        } catch (\Exception $e) {
            $errors[] = 'Forgot Password Mail (CredentialsMail): ' . $e->getMessage();
        }

        // Test File Sending Mail (if test file exists)
        $this->info('Testing File Sending Mail...');
        try {
            // Create a test file
            $testContent = "This is a test file for email attachment testing.\nGenerated on: " . now();
            $testFileName = 'test-document.txt';
            $testFilePath = 'test-files/' . $testFileName;
            
            Storage::disk('public')->put($testFilePath, $testContent);

            Mail::to($email)->send(new SendCompanyFileMail(
                $testFilePath,
                $testFileName,
                'Test - Livraison de document',
                ['recipient_email' => $email]
            ));
            
            // Clean up test file
            Storage::disk('public')->delete($testFilePath);
            
            $successes[] = 'File Sending Mail';
        } catch (\Exception $e) {
            $errors[] = 'File Sending Mail: ' . $e->getMessage();
        }

        // Display results
        $this->newLine();
        if (!empty($successes)) {
            $this->info('✅ Successfully sent:');
            foreach ($successes as $success) {
                $this->line("   - {$success}");
            }
        }

        if (!empty($errors)) {
            $this->error('❌ Failed to send:');
            foreach ($errors as $error) {
                $this->line("   - {$error}");
            }
            return 1;
        }

        $this->info("\n🎉 All email tests completed successfully!");
        return 0;
    }
}
