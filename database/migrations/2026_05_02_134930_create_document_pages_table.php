<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_pages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table
                ->foreignUuid('document_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('locale', 2)->index();
            $table->integer('page_number');
            $table->text('content');
            $table->timestamps();

            $table->unique(['document_id', 'locale', 'page_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_pages');
    }
};
