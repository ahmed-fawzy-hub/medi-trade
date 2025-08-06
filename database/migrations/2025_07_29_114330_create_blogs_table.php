<?php

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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();

            // Main titles
            $table->string('title_en');
            $table->string('title_ar');

            // Descriptions
            $table->text('short_description_en')->nullable(); // وصف مختصر للخارج
            $table->text('short_description_ar')->nullable();
            $table->longText('full_description_en')->nullable(); // وصف داخلي تفصيلي
            $table->longText('full_description_ar')->nullable();

            // Meta
            $table->string('en_meta_title')->nullable();
            $table->text('en_meta_description')->nullable();
            $table->string('ar_meta_title')->nullable();
            $table->text('ar_meta_description')->nullable();

            // External Image
            $table->string('external_image')->nullable();
            $table->string('external_image_alt_en')->nullable();
            $table->string('external_image_alt_ar')->nullable();

            // Internal Image
            $table->string('internal_image')->nullable();
            $table->string('internal_image_alt_en')->nullable();
            $table->string('internal_image_alt_ar')->nullable();
            
            
            $table->string('header_image')->nullable();
            $table->string('header_image_alt_en')->nullable();
            $table->string('header_image_alt_ar')->nullable();

            // SEO + Status
            $table->string('slug_en')->unique();
            $table->string('slug_ar')->unique();
$table->tinyInteger('is_active')->default(1)->check('is_active IN (0,1)');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
