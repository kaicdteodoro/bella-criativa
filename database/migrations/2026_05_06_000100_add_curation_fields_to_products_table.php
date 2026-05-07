<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('curation_status')->default('raw')->after('status');
            $table->unsignedSmallInteger('quality_score')->nullable()->after('source_supplier');
            $table->json('quality_notes')->nullable()->after('quality_score');
            $table->timestamp('processed_at')->nullable()->after('quality_notes');

            $table->index('curation_status');
            $table->index('quality_score');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['curation_status']);
            $table->dropIndex(['quality_score']);
            $table->dropColumn([
                'curation_status',
                'quality_score',
                'quality_notes',
                'processed_at',
            ]);
        });
    }
};
