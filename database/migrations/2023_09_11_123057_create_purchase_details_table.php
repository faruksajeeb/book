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
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onDelete('cascade'); 
            $table->integer('quantity');
            $table->float('unit_price',8,2);
            $table->double('sub_total',10,2);
            $table->decimal('discount_percentage',5,2);
            $table->float('discount_amount',8,2);
            $table->decimal('vat_percentage',5,2);
            $table->float('vat_amount',8,2);
            $table->double('net_sub_total',10,2);
            $table->string('flag',100);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
    }
};
