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
            $table->id();
            $table->string('category_name')->unique();
            $table->string('slug')->unique()->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_popular')->default(0)->nullable();    
            $table->boolean('status')->default(1);           
            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
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
