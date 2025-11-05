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
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); // معرّف أساسي فريد (Auto-incrementing ID)
            $table->string('name')->unique(); // اسم التصنيف (يجب أن يكون فريداً)
            $table->string('slug')->unique(); // رابط مختصر للتصنيف (يساعد في الـ SEO)
            $table->text('description')->nullable(); // وصف اختياري
            $table->timestamps(); // حقلي created_at و updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
