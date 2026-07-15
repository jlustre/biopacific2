<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelWorkbookParser
{
    /**
     * @return array{worksheets: array<int, array{name: string, columns: array<int, string>, data: array<int, array<string, mixed>>}>}
     */
    public function parseUploadedFile(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $worksheets = [];

        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $wsName = $worksheet->getTitle();
            $highestCol = $worksheet->getHighestColumn();
            $highestRow = $worksheet->getHighestRow();
            $headerRow = $worksheet->rangeToArray('A1:' . $highestCol . '1', null, true, true, true)[1];
            $columns = [];

            foreach (array_values($headerRow) as $col) {
                $normalized = $this->normalizeCellValue($col);
                $columns[] = is_string($normalized) ? $normalized : (string) $normalized;
            }

            $rows = $worksheet->rangeToArray('A2:' . $highestCol . $highestRow, null, true, true, true);
            $data = [];

            foreach ($rows as $row) {
                $assoc = [];
                foreach ($columns as $idx => $col) {
                    if ($col === '') {
                        continue;
                    }
                    $cellVal = array_values($row)[$idx] ?? null;
                    $assoc[$col] = $this->normalizeCellValue($cellVal);
                }
                $hasData = collect($assoc)->contains(
                    fn ($value) => $value !== null && $value !== ''
                );
                if ($hasData) {
                    $data[] = $assoc;
                }
            }

            $worksheets[] = [
                'name' => $wsName,
                'columns' => array_values(array_filter($columns, fn ($c) => $c !== '')),
                'data' => $data,
            ];
        }

        return ['worksheets' => $worksheets];
    }

    protected function normalizeCellValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
            $value = $value->getPlainText();
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        if (is_array($value)) {
            foreach ($value as $item) {
                $normalized = $this->normalizeCellValue($item);
                if ($normalized !== null && $normalized !== '') {
                    return $normalized;
                }
            }

            return null;
        }
        if (is_string($value)) {
            $value = str_replace(["\xc2\xa0", "\xE2\x80\x8B", "\xEF\xBB\xBF"], ' ', $value);
            $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

            return trim($value);
        }

        return $value;
    }
}
