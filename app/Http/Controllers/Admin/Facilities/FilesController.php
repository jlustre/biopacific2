<?php


namespace App\Http\Controllers\Admin\Facilities;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\SelectOption;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;   

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
                        $value = $row[$map['worksheet_column']] ?? null;
                        // If the value is not numeric and the target column is integer, try to map via selectoptions
                        $column = $map['table_column'];
                        // Check if the value is not null and not numeric
                        if (!is_null($value) && !is_numeric($value)) {
                            // Try to detect if the target column is an integer column
                            // We'll check the DB schema for the column type
                            try {
                                $type = Schema::getColumnType('bp_employees', $column);
                            } catch (\Throwable $e) {
                                $type = null;
                            }
                            if ($type === 'integer' || $type === 'bigint' || $type === 'smallint') {
                                // Lookup selectoptions by name
                                $option = SelectOption::where('name', $value)->first();
                                if ($option) {
                                    $employeeData[$column] = $option->id;
                                } else {
                                    // Optionally, create the option if not found
                                    $option = SelectOption::create([
                                        'name' => $value,
                                        'type_id' => 1, // Default type, adjust as needed
                                        'isActive' => 1
                                    ]);
                                    $employeeData[$column] = $option->id;
                                }
                            } else {
                                $employeeData[$column] = $value;
                            }
                        } else {
                            $employeeData[$column] = $value;
                        }
                    }
                }
                // Log table/column before type detection

                // Detect column type
                $columnType = null;
                try {
                    $columnType = Schema::getColumnType($table, $column);
                } catch (\Exception $e) {
                    // Ignore
                }

                // Fallback: use DBAL if Schema returns null
                if ($columnType === null) {
                    try {
                        $connection = DB::connection();
                        $schemaManager = $connection->getDoctrineSchemaManager();
                        $doctrineColumns = $schemaManager->listTableColumns($table);
                        if (isset($doctrineColumns[$column])) {
                            $columnType = $doctrineColumns[$column]->getType()->getName();
                        }
                    } catch (\Exception $e) {
                        // Ignore
                    }
                }


                // If column is integer and value is string, map to select_options
                if (($columnType === 'integer' || $columnType === 'bigint') && is_string($value)) {
                    Log::info('DEBUG MAPPING LOGIC TRIGGERED', ['column' => $column, 'value' => $value]);
                    $option = \App\Models\SelectOption::where('name', $value)->first();
                    if ($option) {
                        $employeeData[$column] = $option->id;
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
            Log::info('WORKSHEET DATA MAP', $worksheetDataMap);
        } else {
            $worksheetDataMap = null;
        }
        foreach ($dataRows as $idx => $row) {
            $employeeData = [];
            $addressData = [];
            $phoneData = [];
            foreach ($mappings as $map) {
                if ($map['table'] === 'bp_employees') {
                    $value = $row[$map['worksheet_column']] ?? null;
                    $column = $map['table_column'];
                    // Convert date format for effdt_of_membership if needed
                    if ($column === 'effdt_of_membership' && !empty($value)) {
                        // Accept MM/DD/YYYY or M/D/YYYY and convert to YYYY-MM-DD
                        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $matches)) {
                            $month = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                            $day = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                            $year = $matches[3];
                            $value = "$year-$month-$day";
                        }
                    }
                    // Always apply mapping logic for integer columns
                    try {
                        $type = Schema::getColumnType('bp_employees', $column);
                    } catch (\Throwable $e) {
                        $type = null;
                    }
                    Log::info('DEBUG COLUMN TYPE', [
                        'column' => $column,
                        'value' => $value,
                        'type' => $type
                    ]);
                    if (($type === 'integer' || $type === 'bigint' || $type === 'smallint') && !is_null($value) && !is_numeric($value)) {
                        Log::info('DEBUG MAPPING LOGIC TRIGGERED', [
                            'column' => $column,
                            'value' => $value,
                            'type' => $type
                        ]);
                        $option = \App\Models\SelectOption::where('name', $value)->first();
                        if ($option) {
                            $employeeData[$column] = $option->id;
                        } else {
                            $option = \App\Models\SelectOption::create([
                                'name' => $value,
                                'type_id' => 1, // Default type, adjust as needed
                                'isActive' => 1
                            ]);
                            $employeeData[$column] = $option->id;
                        }
                    } else {
                        $employeeData[$column] = $value;
                    }
                } elseif ($map['table'] === 'bp_emp_addresses') {
                    // Address mapping (same as before)
                    if (isset($worksheetDataMap[$map['worksheet']])) {
                        $empId = $employeeData['employee_num'] ?? $row['Employee ID'] ?? null;
                        $sourceRows = $worksheetDataMap[$map['worksheet']];
                        $sourceRow = null;
                        foreach ($sourceRows as $srcRow) {
                            if ((isset($srcRow['Employee ID']) && $srcRow['Employee ID'] == $empId) || (isset($srcRow['employee_num']) && $srcRow['employee_num'] == $empId)) {
                                $sourceRow = $srcRow;
                                break;
                            }
                        }
                        Log::info('ADDRESS LOOKUP', [
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
                        $empId = $employeeData['employee_num'] ?? $row['Employee ID'] ?? null;
                        $sourceRows = $worksheetDataMap[$map['worksheet']];
                        $sourceRow = null;
                        foreach ($sourceRows as $srcRow) {
                            if ((isset($srcRow['Employee ID']) && $srcRow['Employee ID'] == $empId) || (isset($srcRow['employee_num']) && $srcRow['employee_num'] == $empId)) {
                                $sourceRow = $srcRow;
                                break;
                            }
                        }
                        Log::info('PHONE LOOKUP', [
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
                Log::info('IMPORT ADDRESS DATA', [
                    'row' => $idx,
                    'addressData' => $addressData,
                    'mappings' => $mappings
                ]);
            }
            if (!empty($phoneData)) {
                Log::info('IMPORT PHONE DATA', [
                    'row' => $idx,
                    'phoneData' => $phoneData,
                    'mappings' => $mappings
                ]);
            }
            if (!isset($employeeData['employee_num'])) continue;
            // Validate gender again (should not be needed, but for safety)
            if (array_key_exists('gender', $employeeData)) {
                $gender = $employeeData['gender'];
                if (!in_array($gender, $allowedGenders, true)) {
                    continue;
                }
            }
            $empId = $employeeData['employee_num'];
            Log::info('DEBUG EMPLOYEE DATA BEFORE UPSERT', [
                'employee_num' => $empId,
                'marital_status_id' => $employeeData['marital_status_id'] ?? null,
                'employeeData' => $employeeData
            ]);
            $employee = \App\Models\BPEmployee::updateOrCreate(
                ['employee_num' => $empId],
                $employeeData
            );
            // Insert address if mapped
            if (!empty($addressData)) {
                $addressData['employee_num'] = $empId;
                $addressData['address_type'] = $addressData['address_type'] ?? 'H';
                $addressData['effdt'] = date('Y-m-d');
                $addressData['effseq'] = 0;
                $addressData['country'] = 'USA';
                $addressData['is_primary'] = 1;
                // Only upsert if address1 is present
                if (!empty($addressData['address1'])) {
                    \App\Models\BPEmpAddress::updateOrCreate(
                        [
                            'employee_num' => $empId,
                            'effdt' => $addressData['effdt'],
                            'effseq' => $addressData['effseq']
                        ],
                        $addressData
                    );
                }
                            // Convert date format for effdt_of_membership if needed
                            if ($column === 'effdt_of_membership' && !empty($value)) {
                                // Accept MM/DD/YYYY or M/D/YYYY and convert to YYYY-MM-DD
                                if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $matches)) {
                                    $month = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                                    $day = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                                    $year = $matches[3];
                                    $employeeData[$column] = "$year-$month-$day";
                                } else {
                                    $employeeData[$column] = $value;
                                }
                            }
            }
            // Insert or update phone if mapped
            if (!empty($phoneData)) {
                $phoneData['employee_num'] = $empId;
                $phoneData['phone_type'] = $phoneData['phone_type'] ?? 'M';
                // Default is_primary to 1 unless explicitly 0 or '0'
                $phoneData['is_primary'] = (isset($phoneData['is_primary']) && ($phoneData['is_primary'] == 0 || $phoneData['is_primary'] === '0')) ? 0 : 1;
                // Only upsert if phone_number is present
                if (!empty($phoneData['phone_number'])) {
                    try {
                        Log::info('UPSERT PHONE DATA', [
                            'criteria' => [
                                'employee_num' => $empId,
                                'phone_type' => $phoneData['phone_type']
                            ],
                            'data' => $phoneData
                        ]);
                        \App\Models\BPEmpPhone::updateOrCreate(
                            [
                                'employee_num' => $empId,
                                'phone_type' => $phoneData['phone_type']
                            ],
                            $phoneData
                        );
                    } catch (\Throwable $e) {
                        Log::error('PHONE UPSERT ERROR', [
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
