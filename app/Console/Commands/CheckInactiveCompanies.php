<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\company;
use App\Models\Form;
use Carbon\Carbon;

class CheckInactiveCompanies extends Command
{
    protected $signature = 'companies:check-inactive';
    protected $description = 'Set company status to Inactive if no form sent in the last month';

    public function handle()
    {
        $now = Carbon::now();
        $oneMonthAgo = $now->copy()->subMonth();

        $companies = company::where('status', 'Active')->get();
        foreach ($companies as $company) {
            $hasRecentForm = Form::where('user_id', $company->user_id)
                ->where('created_at', '>=', $oneMonthAgo)
                ->exists();
            if (!$hasRecentForm) {
                $company->status = 'Inactive';
                $company->save();
                $this->info("Company ID {$company->id} set to Inactive.");
            }
        }
        $this->info('Inactive company check complete.');
    }
} 