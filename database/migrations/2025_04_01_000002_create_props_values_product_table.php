<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix . 'product_property_value', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_value_id')->constrained($this->prefix . 'product_prop_values')->onDelete('cascade');
            $table->foreignId('product_id')->constrained($this->prefix . 'products')->onDelete('cascade');
            $table->integer('position')->default(1)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'product_property_value');
    }
};
