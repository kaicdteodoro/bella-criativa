<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('file');
            $table->string('checksum');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'checksum']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_media');
    }
};
