<div id="partF" class="tab-content hidden">
    <h2 class="text-xl font-bold mb-4">PART F - EMPLOYEE PERFORMANCE APPRAISAL</h2>
    @include('admin.facilities.checklist.employee-appraisal-form')

    <!-- PERFORMANCE AREAS (Dynamic from DB) -->
    @include('admin.facilities.checklist.employee-performance-areas')

    @include('admin.facilities.checklist.employee-areas-development')

    <div class="mb-4">
        <h3 class="font-bold mb-2">Signatures</h3>
        <table class="min-w-full border text-xs md:text-sm mb-4">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1">Signatures</th>
                    <th class="border px-2 py-1">Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border px-2 py-1">Supervisor:</td>
                    <td class="border px-2 py-1">{{ $supervisorSignatureDate ?? '' }}</td>
                </tr>
                <tr>
                    <td class="border px-2 py-1">Employee:</td>
                    <td class="border px-2 py-1">{{ $employeeSignatureDate ?? '' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>