<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Synchronous PDF row limit
    |--------------------------------------------------------------------------
    |
    | Larger tabular PDFs are rendered by a queue worker so the browser request
    | remains responsive while DomPDF lays out the document.
    |
    */
    'synchronous_pdf_row_limit' => (int) env('REPORT_SYNC_PDF_ROW_LIMIT', 750),
];
