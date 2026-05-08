<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enable pgvector extension if not exists (only for PostgreSQL)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS vector;');
        }

        Schema::table('document_pages', function (Blueprint $table) {
            // Using dimension 1536 (standard for OpenAI/many models)
            if (DB::getDriverName() === 'pgsql') {
                $table->vector('embedding', 1536)->nullable()->after('content');
            } else {
                $table->text('embedding')->nullable()->after('content');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_pages', function (Blueprint $table) {
            $table->dropColumn('embedding');
        });

        // Optionally, we could drop the extension, but it might be used elsewhere
        // DB::statement('DROP EXTENSION IF EXISTS vector;');
    }
};
