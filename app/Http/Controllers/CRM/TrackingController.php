<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\CampaignLog;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function openTracking(Request $request, $log_id)
    {
        $log = CampaignLog::find($log_id);
        
        if ($log && !$log->opened_at) {
            $log->update([
                'status' => 'opened',
                'opened_at' => now()
            ]);
        }

        // Return a 1x1 transparent GIF pixel
        $pixel = base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
        return response($pixel, 200)->header('Content-Type', 'image/gif');
    }
}
