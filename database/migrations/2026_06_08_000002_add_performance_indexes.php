<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('products', function (Blueprint $table): void {
                $table->fullText('title', 'products_title_fulltext');
            });
        }

        Schema::table('product_media', function (Blueprint $table): void {
            $table->index(['product_id', 'order'], 'product_media_product_order_index');
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropFullTextIndex('products_title_fulltext');
            });
        }

        Schema::table('product_media', function (Blueprint $table): void {
            $table->dropIndex('product_media_product_order_index');
        });
    }
};
