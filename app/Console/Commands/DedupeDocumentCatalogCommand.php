<?php

namespace App\Console\Commands;

use App\Services\ChecklistUploadTypeSyncService;
use App\Services\DocumentCatalogDedupeService;
use Illuminate\Console\Command;

class DedupeDocumentCatalogCommand extends Command
{
    protected $signature = 'documents:dedupe-catalog';

    protected $description = 'Merge duplicate document types into a single catalog name and sync PART A–D checklist items';

    public function handle(DocumentCatalogDedupeService $dedupe, ChecklistUploadTypeSyncService $sync): int
    {
        $result = $dedupe->run();
        $synced = $sync->syncAll();

        $this->info('Document catalog dedupe complete.');
        $this->line('Merged duplicates: ' . $result['merged']);
        $this->line('Renamed types: ' . $result['renamed']);
        $this->line('Remapped uploads: ' . $result['remapped_uploads']);
        $this->line('Remapped requirements: ' . $result['remapped_requirements']);
        $this->line('Checklist items synced: ' . $synced);

        return self::SUCCESS;
    }
}
