<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public $htmlContent;
    public $subjectLine;
    public $campaignLogId;

    public function __construct($htmlContent, $subjectLine, $campaignLogId)
    {
        $this->htmlContent = $htmlContent;
        $this->subjectLine = $subjectLine;
        $this->campaignLogId = $campaignLogId;
    }

    public function build()
    {
        // Inject tracking pixel
        $trackingUrl = route('crm.tracking.open', ['log_id' => $this->campaignLogId]);
        $pixel = '<img src="' . $trackingUrl . '" width="1" height="1" alt="" style="display:none;" />';
        
        $finalHtml = $this->htmlContent . $pixel;

        return $this->subject($this->subjectLine)
                    ->html($finalHtml);
    }
}
