<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WebmasterContact;

class WebmasterContactAdminController extends Controller
{
    public function index()
    {
        $contacts = WebmasterContact::orderByDesc('created_at')->paginate(20);
        return view('admin.webmaster_contacts.index', compact('contacts'));
    }

    public function show(WebmasterContact $contact)
    {
        if (!$contact->is_read) {
            $contact->is_read = true;
            $contact->save();
        }
        return view('admin.webmaster_contacts.show', compact('contact'));
    }
}
