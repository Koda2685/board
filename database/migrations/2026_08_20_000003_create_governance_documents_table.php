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
        if (Schema::hasTable('governance_documents')) {
            return;
        }

        Schema::create('governance_documents', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('reference_no', 100)->nullable()->unique();
            $table->string('document_type', 50)->nullable();
            $table->string('category', 100)->nullable();
            $table->unsignedInteger('version')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('effective_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('file_path', 2048)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'category']);
            $table->index('document_type');
            $table->index('effective_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('governance_documents')) {
            Schema::drop('governance_documents');
        }
    }
};
