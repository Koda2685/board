<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('governance_documents') || Schema::hasColumn('governance_documents', 'document_type')) {
            return;
        }

        Schema::table('governance_documents', function (Blueprint $table): void {
            $table->string('document_type', 50)->nullable()->after('reference_no');
            $table->index('document_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('governance_documents') || ! Schema::hasColumn('governance_documents', 'document_type')) {
            return;
        }

        Schema::table('governance_documents', function (Blueprint $table): void {
            $table->dropIndex(['document_type']);
            $table->dropColumn('document_type');
        });
    }
};
