<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function index()
    {
        // Return a view or JSON listing inquiries
        return view('inquiries.index');
    }

    public function show($inquiry)
    {
        // Return a view for showing an inquiry
        return view('inquiries.show', compact('inquiry'));
    }

    public function destroy($inquiry)
    {
        // Handle deleting an inquiry
        // Example: $inquiry->delete();
        return redirect()->route('inquiries.index');
    }
}
