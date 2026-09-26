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
        if (Schema::hasTable('governance_records')) {
            return;
        }

        Schema::create('governance_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('governance_document_id')->constrained('governance_documents')->cascadeOnDelete();
            $table->string('action', 30);
            $table->text('remarks')->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['governance_document_id', 'recorded_at']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('governance_records')) {
            Schema::drop('governance_records');
        }
    }
};
