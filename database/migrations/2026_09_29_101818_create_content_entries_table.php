<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_entries', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 30);
            $table->string('slug', 160);
            $table->string('title');
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->string('category')->nullable();
            $table->string('image_path')->nullable();
            $table->string('image_alt')->nullable();
            $table->json('details')->nullable();
            $table->boolean('published')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->text('source_url')->nullable();
            $table->timestamps();
            $table->unique(['kind', 'slug']);
            $table->index(['kind', 'published', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_entries');
    }
};
