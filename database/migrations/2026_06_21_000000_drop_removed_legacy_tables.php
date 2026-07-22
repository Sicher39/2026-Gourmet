<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('order_terms_items', 'additional_fee_id')) {
            Schema::table('order_terms_items', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('additional_fee_id');
            });
        }

        foreach ($this->obsoleteTablesInDropOrder() as $table) {
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
        // Removed legacy modules are intentionally not recreated.
    }

    /**
     * @return array<int, string>
     */
    private function obsoleteTablesInDropOrder(): array
    {
        return [
            'audience_group_lecture',
            'audience_group_pricelist_item',
            'audience_group_program_duration',
            'customer_segment_order_terms_section',
            'customer_segment_pricelist_item',
            'lecture_participant_durations',
            'lecture_participant_segment',
            'lecture_pricelist_item',
            'lecture_program_duration',
            'demands',
            'demand_contact_people',
            'demand_subjects',
            'demand_settings',
            'holidays',
            'listener_references',
            'lecturer_references',
            'submitter_references',
            'news_posts',
            'contact_media',
            'contacts',
            'pricelist_prices',
            'pricelist_items',
            'pricelist_categories',
            'additional_fees',
            'audience_groups',
            'customer_segments',
            'participant_segments',
            'program_durations',
            'lectures',
        ];
    }
};
