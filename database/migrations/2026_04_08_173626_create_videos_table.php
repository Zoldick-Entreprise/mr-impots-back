<?php

declare(strict_types=1);

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
        Schema::create('videos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->json('title');
            $table->json('description');
            $table->string('video_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->foreignUuid('category_id')->constrained()->onDelete('cascade');
            $table->boolean('is_featured')->default(false);
            $table->integer('views_count')->default(0);
            $table->datetime('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
