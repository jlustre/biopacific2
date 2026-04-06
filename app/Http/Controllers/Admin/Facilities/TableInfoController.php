<?php

namespace App\Http\Controllers\Admin\Facilities;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class TableInfoController extends Controller
{
    /**
     * Return columns for a given bp_emp_ table.
     */
    public function columns(Request $request)
    {
        $table = $request->input('table');
        Log::info('TableInfoController@columns called', ['table' => $table]);
        if (!$table || (!str_starts_with($table, 'bp_emp_') && $table !== 'bp_employees')) {
            Log::warning('TableInfoController@columns invalid table', ['table' => $table]);
            return response()->json(['error' => 'Invalid table', 'table' => $table], 400);
        }
        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        Log::info('TableInfoController@columns returning columns', ['table' => $table, 'columns' => $columns]);
        return response()->json(['columns' => $columns]);
    }
}
