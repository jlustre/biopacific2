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
            // Log::info('Step 1: Validating file...');
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv',
            ]);
            // Log::info('Step 2: File validated.');

            $file = $request->file('file');
            // Log::info('Step 3: File object obtained.');
            $ext = $file->getClientOriginalExtension();
            // Log::info('Step 4: Extension obtained.', ['ext' => $ext]);

            // Log::info('Step 5: Loading spreadsheet...', ['path' => $file->getPathname()]);
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            // Log::info('Step 6: Spreadsheet loaded.');

            $worksheets = [];
            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $wsName = $worksheet->getTitle();
                $highestCol = $worksheet->getHighestColumn();
                $headerRow = $worksheet->rangeToArray('A1:' . $highestCol . '1', null, true, true, true)[1];
                $worksheets[] = [
                    'name' => $wsName,
                    'columns' => array_values($headerRow),
                ];
            }
            // Log::info('Step 7: Worksheets extracted.', ['worksheets' => $worksheets]);
            return response()->json([
                'worksheets' => $worksheets,
            ]);
        } catch (\Throwable $e) {
            // Log::error('Import error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
