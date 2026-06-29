<?php

namespace Database\Seeders;

use App\Services\PositionDocumentRequirementsSeedService;
use Illuminate\Database\Seeder;

/**
 * Seeds default position → required document mappings from
 * database/seeders/data/position_document_requirements.php
 *
 * Idempotent: by default only positions with no existing requirements are updated.
 * Set SEED_POSITION_DOCUMENT_REQUIREMENTS_FORCE=true to overwrite all mapped positions.
 *
 * Run alone:
 *   php artisan db:seed --class=PositionDocumentRequirementsSeeder
 */
class PositionDocumentRequirementsSeeder extends Seeder
{
    public function run(): void
    {
        $onlyWhenEmpty = ! filter_var(
            config('seeding.position_document_requirements_force', false),
            FILTER_VALIDATE_BOOLEAN
        );

        $result = app(PositionDocumentRequirementsSeedService::class)->seed(
            onlyWhenEmpty: $onlyWhenEmpty,
            includeUnmappedPositions: true
        );

        $this->command?->info(sprintf(
            'Position document requirements: %d position(s) updated, %d skipped (already configured), %d type assignment(s) synced.',
            $result['positions_processed'],
            $result['positions_skipped'],
            $result['requirements_synced']
        ));

        if (! empty($result['positions_missing'])) {
            $this->command?->warn('No matching upload types for: ' . implode(', ', $result['positions_missing']));
        }

        if (! empty($result['types_missing'])) {
            $this->command?->warn('Missing upload types (run DocumentsManagementSeeder first): ' . implode(', ', array_slice($result['types_missing'], 0, 10)) . (count($result['types_missing']) > 10 ? '…' : ''));
        }
    }
}
