<?php

namespace Tests\Unit;

use App\Services\ExcelWorkbookParser;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelWorkbookParserTest extends TestCase
{
    public function test_it_excludes_formatted_but_empty_rows_from_import_data(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Profile');
        $sheet->setCellValue('A1', 'Employee ID');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('A2', 'E-100');
        $sheet->setCellValue('B2', 'Jane Employee');
        $sheet->getStyle('A1052:B1052')->getFont()->setName('Arial');

        $path = tempnam(sys_get_temp_dir(), 'employee-import-').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        try {
            $result = app(ExcelWorkbookParser::class)->parseUploadedFile(
                new UploadedFile($path, 'employees.xlsx', null, null, true)
            );

            $this->assertCount(1, $result['worksheets'][0]['data']);
            $this->assertSame('E-100', $result['worksheets'][0]['data'][0]['Employee ID']);
        } finally {
            @unlink($path);
            $spreadsheet->disconnectWorksheets();
        }
    }
}
