<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\EmployeesController;
use App\Http\Controllers\Concerns\ProvidesMemberPortalContext;
use App\Models\BPEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmploymentController extends Controller
{
    use ProvidesMemberPortalContext;

    /**
     * Display the authenticated employee's own employment record.
     */
    public function portal(Request $request)
    {
        $user = Auth::user();
        $employee = $this->resolveOwnEmployee();

        if (! $employee) {
            return view('employment.no-record', array_merge($this->memberPortalContext($user), [
                'portalActive' => 'employment',
                'portalTitle' => 'My Employment | Bio Pacific HR Portal',
                'portalEyebrow' => 'Employment',
                'portalPageTitle' => 'My Employment',
                'showPortalSearch' => false,
                'showPortalNotifications' => true,
            ]));
        }

        if (! $request->filled('facility') && $employee->currentAssignment?->facility_id) {
            $request->merge(['facility' => $employee->currentAssignment->facility_id]);
        }

        $request->attributes->set('employee_edit_options', [
            'isSelfService' => true,
        ]);

        $view = app(EmployeesController::class)->edit($request, $employee->id);

        return $view->with(array_merge($this->memberPortalContext($user), [
            'portalActive' => 'employment',
            'portalTitle' => 'My Employment | Bio Pacific HR Portal',
            'portalEyebrow' => 'Employment',
            'portalPageTitle' => 'My Employment',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
            'isSelfService' => true,
        ]));
    }

    public function updatePersonal(Request $request)
    {
        return app(EmployeesController::class)->updatePersonal($request, $this->resolveOwnEmployeeOrFail()->id);
    }

    public function updateAddress(Request $request)
    {
        return app(EmployeesController::class)->updateAddress($request, $this->resolveOwnEmployeeOrFail()->id);
    }

    public function updateTaxData(Request $request)
    {
        return app(EmployeesController::class)->updateTaxData($request, $this->resolveOwnEmployeeOrFail()->id);
    }

    public function addPhone(Request $request)
    {
        return app(EmployeesController::class)->addPhone($request, $this->resolveOwnEmployeeOrFail()->id);
    }

    public function updatePhone(Request $request, $phone)
    {
        return app(EmployeesController::class)->updatePhone($request, $this->resolveOwnEmployeeOrFail()->id, $phone);
    }

    public function uploadDocument(Request $request)
    {
        return app(EmployeesController::class)->uploadDocument($request, $this->resolveOwnEmployeeOrFail()->id);
    }

    public function updateDocument(Request $request, $document)
    {
        return app(EmployeesController::class)->updateDocument($request, $this->resolveOwnEmployeeOrFail()->id, $document);
    }

    public function deleteDocument($document)
    {
        return app(EmployeesController::class)->deleteDocument($this->resolveOwnEmployeeOrFail()->id, $document);
    }

    public function viewDocument($document)
    {
        return app(EmployeesController::class)->viewDocument($this->resolveOwnEmployeeOrFail()->id, $document);
    }

    public function downloadDocument($document)
    {
        return app(EmployeesController::class)->downloadDocument($this->resolveOwnEmployeeOrFail()->id, $document);
    }

    public function previewDocumentNotification(Request $request, $document)
    {
        $employee = $this->resolveOwnEmployeeOrFail();

        return app(EmployeesController::class)->previewDocumentNotification($request, $employee->id, $document);
    }

    public function sendDocumentNotification(Request $request, $document)
    {
        $employee = $this->resolveOwnEmployeeOrFail();

        return app(EmployeesController::class)->sendDocumentNotification($request, $employee->id, $document);
    }

    protected function resolveOwnEmployee(): ?BPEmployee
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        return $user->resolvedBpEmployee([
            'phones',
            'addresses',
            'user',
            'taxData',
            'assignments.hourlyStatus',
            'assignments.compensationRate',
            'assignments.facility',
            'assignments.department',
            'assignments.position',
            'currentAssignment',
            'currentAssignment.facility',
            'currentAssignment.position',
            'currentAssignment.position.reportsToPosition',
            'currentAssignment.hourlyStatus',
            'currentAssignment.compensationRate',
        ]);
    }

    protected function resolveOwnEmployeeOrFail(): BPEmployee
    {
        return $this->resolveOwnEmployee() ?? abort(404, 'No employee record linked to your account.');
    }
}
