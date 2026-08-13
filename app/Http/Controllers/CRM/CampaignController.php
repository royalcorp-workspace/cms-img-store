<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Campaign;
use App\Models\CRM\EmailTemplate;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::with('template')->latest()->paginate(10);
        return view('pages.crm.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $templates = EmailTemplate::where('is_active', true)->get();
        return view('pages.crm.campaigns.create', compact('templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'email_template_id' => 'required|exists:email_templates,id',
            'scheduled_at' => 'nullable|date',
        ]);

        $campaign = Campaign::create([
            'name' => $request->name,
            'subject' => $request->subject,
            'email_template_id' => $request->email_template_id,
            'status' => 'draft',
            'scheduled_at' => $request->scheduled_at,
        ]);

        return redirect()->route('admin.crm.campaigns.index')->with('success', 'Campaign berhasil dibuat.');
    }

    public function edit(Campaign $campaign)
    {
        $templates = EmailTemplate::where('is_active', true)->get();
        return view('pages.crm.campaigns.edit', compact('campaign', 'templates'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'email_template_id' => 'required|exists:email_templates,id',
            'scheduled_at' => 'nullable|date',
        ]);

        $campaign->update($request->only('name', 'subject', 'email_template_id', 'scheduled_at'));

        return redirect()->route('admin.crm.campaigns.index')->with('success', 'Campaign berhasil diupdate.');
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        return back()->with('success', 'Campaign dihapus.');
    }

    public function blast(Campaign $campaign)
    {
        $campaign->update(['status' => 'processing']);
        
        \App\Jobs\ProcessCampaignJob::dispatch($campaign);
        
        return back()->with('success', 'Proses pengiriman email sedang berjalan di background (via Job).');
    }
}
