<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('governance_documents')) {
            return;
        }

        DB::table('governance_documents')
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    $updates = [];

                    if ((is_null($row->title) || trim((string) $row->title) === '') && ! is_null($row->doc_type) && trim((string) $row->doc_type) !== '') {
                        $updates['title'] = (string) $row->doc_type;
                    }

                    if (is_null($row->issued_at) && ! is_null($row->uploaded_at)) {
                        $updates['issued_at'] = $row->uploaded_at;
                    }

                    if (is_null($row->effective_at) && ! is_null($row->uploaded_at)) {
                        $updates['effective_at'] = $row->uploaded_at;
                    }

                    if ((is_null($row->status) || trim((string) $row->status) === '')) {
                        $updates['status'] = 'draft';
                    }

                    if (is_null($row->created_at) && ! is_null($row->uploaded_at)) {
                        $updates['created_at'] = $row->uploaded_at;
                    }

                    if (is_null($row->updated_at) && ! is_null($row->uploaded_at)) {
                        $updates['updated_at'] = $row->uploaded_at;
                    }

                    if ($updates !== []) {
                        DB::table('governance_documents')
                            ->where('id', $row->id)
                            ->update($updates);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback: this migration fills missing values from legacy fields.
    }
};
