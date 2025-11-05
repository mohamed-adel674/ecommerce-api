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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // مفتاح خارجي يربط المنتج بالتصنيف
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2); // السعر (8 خانات إجمالاً، 2 بعد الفاصلة)
            $table->unsignedInteger('stock_quantity')->default(0); // كمية المخزون (لا تكون سالبة)
            $table->boolean('is_active')->default(true); // هل المنتج معروض للبيع؟
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
