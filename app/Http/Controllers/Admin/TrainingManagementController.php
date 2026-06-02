<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TrainingManagementService;
use Illuminate\Http\Request;

class TrainingManagementController extends Controller
{
    public function index(Request $request, TrainingManagementService $trainingManagement)
    {
        $user = $request->user();

        if (!$user || !$user->hasRole(['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd', 'don'])) {
            abort(403, 'You do not have permission to access training management.');
        }

        $page = $trainingManagement->buildPage($user, $request);

        return view('admin.training-management.index', $page);
    }
}
