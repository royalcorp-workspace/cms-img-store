<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::latest()->paginate(10);
        return view('pages.crm.email-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('pages.crm.email-templates.builder');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'html_content' => 'required|string'
        ]);

        $template = EmailTemplate::create($request->all());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => route('admin.crm.email-templates.index')
            ]);
        }

        return redirect()->route('admin.crm.email-templates.index');
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        return view('pages.crm.email-templates.builder', compact('emailTemplate'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'html_content' => 'required|string'
        ]);

        $emailTemplate->update($request->all());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Template updated'
            ]);
        }
        return back();
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();
        return redirect()->route('admin.crm.email-templates.index')->with('success', 'Template deleted');
    }
}
