<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CRM\Lead;
use App\Models\CRM\LeadStage;

class LeadController extends Controller
{
    public function index()
    {
        if (LeadStage::count() === 0) {
            $this->seedDefaultStages();
        }

        $stages = LeadStage::orderBy('order_index')->with(['leads.customer'])->get();
        return view('pages.crm.kanban', compact('stages'));
    }

    public function updateStage(Request $request, Lead $lead)
    {
        $request->validate([
            'stage_id' => 'required|exists:lead_stages,id'
        ]);

        $lead->update([
            'stage_id' => $request->stage_id
        ]);

        return response()->json(['success' => true, 'message' => 'Stage updated']);
    }

    private function seedDefaultStages()
    {
        $defaultStages = [
            ['name' => 'New Leads', 'color' => '#3b82f6', 'order_index' => 1, 'is_system' => true],
            ['name' => 'Pending', 'color' => '#eab308', 'order_index' => 2, 'is_system' => true],
            ['name' => 'On Process', 'color' => '#8b5cf6', 'order_index' => 3, 'is_system' => true],
            ['name' => 'Delivery', 'color' => '#f97316', 'order_index' => 4, 'is_system' => true],
            ['name' => 'Closing', 'color' => '#22c55e', 'order_index' => 5, 'is_system' => true],
            ['name' => 'Lost', 'color' => '#ef4444', 'order_index' => 6, 'is_system' => true],
        ];

        foreach ($defaultStages as $stage) {
            LeadStage::create($stage);
        }
    }
}
