<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('status', ['draft', 'published'])->default('published');
            $table->text('short_description')->nullable();
            $table->longText('technical_description')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('og_image')->nullable();
            $table->json('available_colors')->nullable();
            $table->json('materials')->nullable();
            $table->string('supplier_code')->nullable();
            $table->string('source_supplier')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
