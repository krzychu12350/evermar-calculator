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
        Schema::create('panel_variant_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('variants')->onDelete('cascade');
//            $table->string('model');
            $table->foreignId('panel_model_id')->constrained('panel_models')->onDelete('cascade'); // 🔄 replace string with relation
            $table->enum('install_type', ['string', 'with_storage']);
            $table->decimal('price_per_panel', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panel_variant_prices');
    }
};
