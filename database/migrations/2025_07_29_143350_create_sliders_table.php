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
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('title_en')->nullable();
            $table->string('title_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('image')->nullable();
            $table->string('video')->nullable();
            $table->string('en_image_alt')->nullable(); // alt attribute for image
            $table->string('ar_image_alt')->nullable(); // alt attribute for image
            $table->string('en_video_alt')->nullable(); // alt attribute for video
            $table->string('ar_video_alt')->nullable(); // alt attribute for video
$table->tinyInteger('is_active')->default(1)->check('is_active IN (0,1)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};
