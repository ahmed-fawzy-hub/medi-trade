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
        Schema::create('seo_tags', function (Blueprint $table) {
            $table->id();
            $table->string('en_meta_title')->nullable();
            $table->text('en_meta_description')->nullable();
            $table->string('ar_meta_title')->nullable();
            $table->text('ar_meta_description')->nullable();
            $table->string('page_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_tags');
    }
};
