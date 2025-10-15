<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobApplication;

class JobApplicationController extends Controller
{
    public function show($id)
    {
        $application = JobApplication::findOrFail($id);
        return view('applications.show', compact('application'));
    }
}
