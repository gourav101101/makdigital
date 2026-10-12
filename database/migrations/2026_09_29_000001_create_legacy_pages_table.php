<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legacy_pages', function (Blueprint $table) {
            $table->id();
            $table->string('source_url', 768)->unique();
            $table->text('title');
            $table->longText('content');
            $table->json('metadata');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_pages');
    }
};
