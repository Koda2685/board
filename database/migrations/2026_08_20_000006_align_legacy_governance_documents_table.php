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
        if (! Schema::hasTable('governance_documents')) {
            return;
        }

        Schema::table('governance_documents', function (Blueprint $table): void {
            if (! Schema::hasColumn('governance_documents', 'title')) {
                $table->string('title')->nullable()->after('id');
            }

            if (! Schema::hasColumn('governance_documents', 'reference_no')) {
                $table->string('reference_no', 100)->nullable()->after('title');
            }

            if (! Schema::hasColumn('governance_documents', 'category')) {
                $table->string('category', 100)->nullable()->after('reference_no');
            }

            if (! Schema::hasColumn('governance_documents', 'version')) {
                $table->unsignedInteger('version')->nullable()->after('category');
            }

            if (! Schema::hasColumn('governance_documents', 'status')) {
                $table->string('status', 20)->nullable()->default('draft')->after('version');
            }

            if (! Schema::hasColumn('governance_documents', 'issued_at')) {
                $table->timestamp('issued_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('governance_documents', 'effective_at')) {
                $table->timestamp('effective_at')->nullable()->after('issued_at');
            }

            if (! Schema::hasColumn('governance_documents', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('effective_at');
            }

            if (! Schema::hasColumn('governance_documents', 'notes')) {
                $table->text('notes')->nullable()->after('file_path');
            }

            if (! Schema::hasColumn('governance_documents', 'uploaded_by')) {
                $table->foreignId('uploaded_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('governance_documents', 'created_at') && ! Schema::hasColumn('governance_documents', 'updated_at')) {
                $table->timestamps();
            } else {
                if (! Schema::hasColumn('governance_documents', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }

                if (! Schema::hasColumn('governance_documents', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left empty to avoid destructive rollback on legacy data tables.
    }
};
