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
        Schema::create('about_us', function (Blueprint $table) {
    $table->id();
    $table->string('title_en')->nullable();
    $table->string('title_ar')->nullable();

    $table->text('home_description_en')->nullable();
    $table->text('home_description_ar')->nullable();

    $table->text('about_description_en')->nullable();
    $table->text('about_description_ar')->nullable();

    $table->text('mission_en')->nullable();
    $table->text('mission_ar')->nullable();

    $table->text('vision_en')->nullable();
    $table->text('vision_ar')->nullable();

    $table->text('investments_en')->nullable();
    $table->text('investments_ar')->nullable();

    $table->string('image')->nullable();
    $table->string('en_alt_image')->nullable();
    $table->string('ar_alt_image')->nullable();
    
    $table->text('why_medi_trade_en')->nullable();
            $table->text('why_medi_trade_ar')->nullable();

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_us');
    }
};
