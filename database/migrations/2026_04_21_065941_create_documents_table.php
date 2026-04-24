<?php

declare(strict_types=1);

use App\Enums\DocumentStatus;
use App\Enums\OcrStatus;
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
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->json('title');
            $table->foreignUuid('category_id');
            $table->string('status')->default(DocumentStatus::DRAFT->value)->index();
            $table->string('ocr_status')->default(OcrStatus::PENDING->value);
            $table->foreignUuid('uploaded_by');
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('document_views')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
