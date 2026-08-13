<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\CRM\Campaign;
use App\Models\CRM\CampaignLog;
use App\Models\Customer\Customer;
use App\Mail\CampaignMail;

class ProcessCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaign;

    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function handle()
    {
        // Get customers with valid email
        // For testing, we just get them all. In production, use chunking.
        $customers = Customer::whereNotNull('email')->where('email', '!=', '')->get();

        $this->campaign->load('template');
        $htmlContent = $this->campaign->template->html_content;
        $subject = $this->campaign->subject;

        foreach ($customers as $customer) {
            // Create Campaign Log
            $log = CampaignLog::create([
                'campaign_id' => $this->campaign->id,
                'customer_id' => $customer->id,
                'status' => 'sent',
                'opened_at' => null,
                'clicked_at' => null,
            ]);

            try {
                // Send Mail
                Mail::to($customer->email)->send(new CampaignMail($htmlContent, $subject, $log->id));
            } catch (\Exception $e) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
        }

        // Mark campaign as completed
        $this->campaign->update(['status' => 'completed']);
    }
}
