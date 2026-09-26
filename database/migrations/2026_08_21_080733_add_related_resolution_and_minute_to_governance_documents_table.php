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
        Schema::table('governance_documents', function (Blueprint $table): void {
            $table->foreignId('related_resolution_id')->nullable()->after('document_type')->constrained('governance_documents')->nullOnDelete();
            $table->foreignId('related_minute_id')->nullable()->after('related_resolution_id')->constrained('governance_documents')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('governance_documents', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('related_minute_id');
            $table->dropConstrainedForeignId('related_resolution_id');
        });
    }
};
