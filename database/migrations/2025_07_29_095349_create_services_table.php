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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title_en');
            $table->string('title_ar');

            // ✅ وصف مختصر (للعرض الخارجي)
            $table->text('short_description_en')->nullable();
            $table->text('short_description_ar')->nullable();

            // ✅ وصف كامل (داخل صفحة التفاصيل)
            $table->longText('full_description_en')->nullable();
            $table->longText('full_description_ar')->nullable();

            $table->string('en_meta_title')->nullable();
            $table->text('en_meta_description')->nullable();
            $table->string('ar_meta_title')->nullable();
            $table->text('ar_meta_description')->nullable();
            $table->string('slug_en')->unique();
            $table->string('slug_ar')->unique();

            // ✅ 3 أنواع صور
            $table->string('main_image')->nullable();
            $table->string('header_image')->nullable();

            // ✅ alt attributes لكل صورة

            $table->string('main_image_alt_en')->nullable();
            $table->string('main_image_alt_ar')->nullable();
            $table->string('header_image_alt_en')->nullable();
            $table->string('header_image_alt_ar')->nullable();

            $table->longText('supplies_text_en')->nullable();
            $table->longText('supplies_text_ar')->nullable();
            $table->string('supplies_image')->nullable();
            $table->string('supplies_image_alt_en')->nullable();
            $table->string('supplies_image_alt_ar')->nullable();
$table->tinyInteger('is_active')->default(1)->check('is_active IN (0, 1)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
