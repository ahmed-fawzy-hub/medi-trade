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
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('image');
            $table->string('en_alt_image')->nullable();
            $table->string('ar_alt_image')->nullable(); // alt attribute for image
    $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
    
$table->tinyInteger('is_active')->default(1)->check('is_active IN (0, 1)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
