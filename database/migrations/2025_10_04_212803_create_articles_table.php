<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('author')->nullable();
            $table->foreignId('source_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->onDelete('set null')
                ->onUpdate('cascade');
            $table->string('url', 500)->unique();
            $table->string('image_url', 500)->nullable();
            $table->timestamp('published_at');
            $table->string('external_id')->unique();
            $table->timestamps();

            $table->index('published_at');
            $table->index('author');
            $table->index(['source_id', 'published_at']);
            $table->index(['category_id', 'published_at']);
            $table->fullText(['title', 'description', 'content']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('articles');
        Schema::enableForeignKeyConstraints();

    }
};