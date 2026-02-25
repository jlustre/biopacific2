<?php

namespace App\Http\Controllers;

use App\Models\PreEmploymentApplication;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class HireApplicantController extends Controller
{
    /**
     * Update the pre-employment application status to hired and copy data to employees table
     */
    public function hire(Request $request, PreEmploymentApplication $preEmployment)
    {
        // Check authorization
        Gate::authorize('hireApplicant', $preEmployment);

        $validated = $request->validate([
            'hire_date' => 'required|date',
            'position_id' => 'required|exists:positions,id', // or your position table
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Update status to hired
            $preEmployment->update([
                'status' => 'hired',
                'hired_at' => now(),
            ]);

            // Copy data from pre-employment to employee
            $employee = $preEmployment->copyToEmployee($validated);

            DB::commit();
            return redirect()
                ->route('admin.employees.show', $employee)
                ->with('success', 'Applicant hired successfully! Data has been transferred to the employees table.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Hiring applicant error: ' . $e->getMessage(), [
                'pre_employment_id' => $preEmployment->id,
                'exception' => $e
            ]);
            return redirect()
                ->back()
                ->withErrors(['error' => 'An error occurred while hiring the applicant. Please try again. Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject a pre-employment application
     */
    public function reject(Request $request, PreEmploymentApplication $preEmployment)
    {
        Gate::authorize('rejectApplicant', $preEmployment);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $preEmployment->update([
                'status' => 'rejected',
                'rejection_reason' => $validated['rejection_reason'],
                'rejected_at' => now(),
            ]);

            DB::commit();
            return redirect()
                ->back()
                ->with('success', 'Application rejected successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rejecting application error: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withErrors(['error' => 'An error occurred while rejecting the application.']);
        }
    }
}
