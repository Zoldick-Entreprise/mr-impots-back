<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ocr_jobs', function (Blueprint $table) {
            $table->string('locale', 2)->after('document_id')->default('fr');
            // We should ensure a document has only one OCR Job per locale
            $table->unique(['document_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::table('ocr_jobs', function (Blueprint $table) {
            $table->dropUnique(['document_id', 'locale']);
            $table->dropColumn('locale');
        });
    }
};
