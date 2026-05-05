<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ocr_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table
                ->foreignUuid('document_id')
                ->constrained()
                ->cascadeOnDelete();
            $table
                ->enum('status', [
                    'pending',
                    'processing',
                    'completed',
                    'failed',
                ])
                ->default('pending')
                ->index();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocr_jobs');
    }
};
