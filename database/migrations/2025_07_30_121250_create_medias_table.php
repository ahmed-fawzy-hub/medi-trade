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
        Schema::create('medias', function (Blueprint $table) {
    $table->id();
    $table->enum('type', ['image', 'video']);
    $table->string('file_path'); // تم تغيير 'file' إلى 'file_path'
    $table->string('video_url')->nullable(); // في حالة الفيديو فقط
    $table->string('alt_text')->nullable(); // alt text للصور

    

$table->tinyInteger('is_active')->default(1)->check('is_active IN (0,1)');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medias');
    }
};
