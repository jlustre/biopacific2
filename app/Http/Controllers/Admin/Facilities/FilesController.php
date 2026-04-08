<?php


namespace App\Http\Controllers\Admin\Facilities;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FilesController extends Controller
{
    /**
     * Handle import of Excel file for facility data.
     */
    public function import(Request $request, $facility)
    {
        Log::info('FilesController@import hit', ['facility' => $facility]);
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv',
            ]);
            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheets = [];
            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $wsName = $worksheet->getTitle();
                $highestCol = $worksheet->getHighestColumn();
                $highestRow = $worksheet->getHighestRow();
                $headerRow = $worksheet->rangeToArray('A1:' . $highestCol . '1', null, true, true, true)[1];
                $columns = array_values($headerRow);
                // Get all data rows as associative arrays
                $rows = $worksheet->rangeToArray('A2:' . $highestCol . $highestRow, null, true, true, true);
                $data = [];
                foreach ($rows as $row) {
                    $assoc = [];
                    foreach ($columns as $idx => $col) {
                        $colKey = $col;
                        $cellVal = array_values($row)[$idx] ?? null;
                        $assoc[$colKey] = $cellVal;
                    }
                    $data[] = $assoc;
                }
                $worksheets[] = [
                    'name' => $wsName,
                    'columns' => $columns,
                    'data' => $data,
                ];
            }
            return response()->json([
                'worksheets' => $worksheets,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import mapped data to bp_employees, check for duplicates, and handle upserts.
     * Expects: mappings, data, confirm_overwrite (bool), facility
     */
    public function importData(Request $request, $facility)
    {
        $mappings = $request->input('mappings', []);
        $dataRows = $request->input('data', []);
        $confirmOverwrite = $request->boolean('confirm_overwrite', false);
        $duplicates = [];
        $imported = [];

        $invalidRows = [];
        $allowedGenders = ['M', 'F', 'O', 'N', null, ''];
        foreach ($dataRows as $idx => $row) {
            // Build employee data from mapping
            $employeeData = [];
            foreach ($mappings as $map) {
                if ($map['table'] === 'bp_employees') {
                    $employeeData[$map['table_column']] = $row[$map['worksheet_column']] ?? null;
                }
            }
        }

        if (count($invalidRows) > 0) {
            return response()->json([
                'invalid_rows' => $invalidRows,
                'message' => 'Invalid gender values found. Allowed: M, F, O, N.',
            ], 422);
        }
        if (count($duplicates) > 0 && !$confirmOverwrite) {
            return response()->json([
                'duplicates' => $duplicates,
                'message' => 'Duplicate employee IDs found. Confirm overwrite?',
            ], 409);
        }

        // Import or update
        // Build worksheet data map for cross-worksheet lookups
        $worksheetDataMap = [];
        if (!empty($request->input('worksheets'))) {
            foreach ($request->input('worksheets') as $ws) {
                $worksheetDataMap[$ws['name']] = $ws['data'];
            }
            \Illuminate\Support\Facades\Log::info('WORKSHEET DATA MAP', $worksheetDataMap);
        } else {
            $worksheetDataMap = null;
        }
        foreach ($dataRows as $idx => $row) {
            $employeeData = [];
            $addressData = [];
            $phoneData = [];
            foreach ($mappings as $map) {
                if ($map['table'] === 'bp_employees') {
                    $employeeData[$map['table_column']] = $row[$map['worksheet_column']] ?? null;
                } elseif ($map['table'] === 'bp_emp_addresses') {
                    // Address mapping (same as before)
                    if (isset($worksheetDataMap[$map['worksheet']])) {
                        $empId = $employeeData['emp_id'] ?? $row['Employee ID'] ?? null;
                        $sourceRows = $worksheetDataMap[$map['worksheet']];
                        $sourceRow = null;
                        foreach ($sourceRows as $srcRow) {
                            if ((isset($srcRow['Employee ID']) && $srcRow['Employee ID'] == $empId) || (isset($srcRow['emp_id']) && $srcRow['emp_id'] == $empId)) {
                                $sourceRow = $srcRow;
                                break;
                            }
                        }
                        \Illuminate\Support\Facades\Log::info('ADDRESS LOOKUP', [
                            'empId' => $empId,
                            'worksheet' => $map['worksheet'],
                            'foundRow' => $sourceRow,
                            'allRows' => $sourceRows
                        ]);
                        $addressData[$map['table_column']] = $sourceRow[$map['worksheet_column']] ?? null;
                    } else {
                        $addressData[$map['table_column']] = $row[$map['worksheet_column']] ?? null;
                    }
                } elseif ($map['table'] === 'bp_emp_phones') {
                    // Phone mapping
                    if (isset($worksheetDataMap[$map['worksheet']])) {
                        $empId = $employeeData['emp_id'] ?? $row['Employee ID'] ?? null;
                        $sourceRows = $worksheetDataMap[$map['worksheet']];
                        $sourceRow = null;
                        foreach ($sourceRows as $srcRow) {
                            if ((isset($srcRow['Employee ID']) && $srcRow['Employee ID'] == $empId) || (isset($srcRow['emp_id']) && $srcRow['emp_id'] == $empId)) {
                                $sourceRow = $srcRow;
                                break;
                            }
                        }
                        \Illuminate\Support\Facades\Log::info('PHONE LOOKUP', [
                            'empId' => $empId,
                            'worksheet' => $map['worksheet'],
                            'foundRow' => $sourceRow,
                            'allRows' => $sourceRows
                        ]);
                        $phoneData[$map['table_column']] = $sourceRow[$map['worksheet_column']] ?? null;
                    } else {
                        $phoneData[$map['table_column']] = $row[$map['worksheet_column']] ?? null;
                    }
                }
            }
            // Debug: Log address and phone mapping and data
            if (!empty($addressData)) {
                \Illuminate\Support\Facades\Log::info('IMPORT ADDRESS DATA', [
                    'row' => $idx,
                    'addressData' => $addressData,
                    'mappings' => $mappings
                ]);
            }
            if (!empty($phoneData)) {
                \Illuminate\Support\Facades\Log::info('IMPORT PHONE DATA', [
                    'row' => $idx,
                    'phoneData' => $phoneData,
                    'mappings' => $mappings
                ]);
            }
            if (!isset($employeeData['emp_id'])) continue;
            // Validate gender again (should not be needed, but for safety)
            if (array_key_exists('gender', $employeeData)) {
                $gender = $employeeData['gender'];
                if (!in_array($gender, $allowedGenders, true)) {
                    continue;
                }
            }
            $empId = $employeeData['emp_id'];
            $employee = \App\Models\BPEmployee::updateOrCreate(
                ['emp_id' => $empId],
                $employeeData
            );
            // Insert address if mapped
            if (!empty($addressData)) {
                $addressData['emp_id'] = $empId;
                $addressData['address_type'] = $addressData['address_type'] ?? 'H';
                $addressData['effdt'] = date('Y-m-d');
                $addressData['effseq'] = 0;
                $addressData['country'] = 'USA';
                $addressData['is_primary'] = 1;
                // Only upsert if address1 is present
                if (!empty($addressData['address1'])) {
                    \App\Models\BPEmpAddress::updateOrCreate(
                        [
                            'emp_id' => $empId,
                            'effdt' => $addressData['effdt'],
                            'effseq' => $addressData['effseq']
                        ],
                        $addressData
                    );
                }
            }
            // Insert or update phone if mapped
            if (!empty($phoneData)) {
                $phoneData['emp_id'] = $empId;
                $phoneData['phone_type'] = $phoneData['phone_type'] ?? 'M';
                // Default is_primary to 1 unless explicitly 0 or '0'
                $phoneData['is_primary'] = (isset($phoneData['is_primary']) && ($phoneData['is_primary'] == 0 || $phoneData['is_primary'] === '0')) ? 0 : 1;
                // Only upsert if phone_number is present
                if (!empty($phoneData['phone_number'])) {
                    try {
                        \Illuminate\Support\Facades\Log::info('UPSERT PHONE DATA', [
                            'criteria' => [
                                'emp_id' => $empId,
                                'phone_type' => $phoneData['phone_type']
                            ],
                            'data' => $phoneData
                        ]);
                        \App\Models\BPEmpPhone::updateOrCreate(
                            [
                                'emp_id' => $empId,
                                'phone_type' => $phoneData['phone_type']
                            ],
                            $phoneData
                        );
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error('PHONE UPSERT ERROR', [
                            'error' => $e->getMessage(),
                            'data' => $phoneData
                        ]);
                    }
                }
            }
            $imported[] = $empId;
        }

        return response()->json([
            'success' => true,
            'imported' => $imported,
        ]);
    }

}
