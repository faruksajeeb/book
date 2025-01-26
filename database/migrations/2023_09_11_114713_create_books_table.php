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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('isbn')->nullable()->comment('International Standard Book Number');
            // $table->bigInteger('author_id')->unsigned();
            $table->bigInteger('category_id')->unsigned();
            $table->bigInteger('sub_category_id')->unsigned();
            $table->string('genre')->nullable();
            $table->decimal('price',8,2)->nullable()->comment('Current Price');
            $table->decimal('buying_discount_percentage',5,2);
            $table->decimal('selling_discount_percentage',5,2);
            $table->decimal('buying_vat_percentage',5,2);
            $table->decimal('selling_vat_percentage',5,2);
            $table->string('photo')->nullable();
            $table->string('stock_quantity')->default(0);
            $table->string('publication_year')->nullable();
            $table->bigInteger('publisher_id')->unsigned();
            $table->boolean('status')->default(true); 
            $table->dateTimeTz('created_at', $precision = 0);
            $table->dateTimeTz('updated_at', $precision = 0);
            $table->softDeletesTz('deleted_at', $precision = 0);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            // $table->foreign('author_id')->references('id')->on('authors')->onDelete('cascade');
            $table->foreign('publisher_id')->references('id')->on('publishers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
