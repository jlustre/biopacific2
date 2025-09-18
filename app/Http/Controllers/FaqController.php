<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faq;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $faqs = Faq::all();
        $categories = Faq::select('category')->distinct()->pluck('category')->filter()->values()->all();
        $variant = $request->query('variant', 'default');
        $view = 'partials.faqs.' . $variant;
        return view($view, compact('faqs', 'categories'));
    }
}
