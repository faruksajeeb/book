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
        DB::statement('ALTER TABLE `customers` CHANGE `name` `customer_name` VARCHAR(255)');
        DB::statement('ALTER TABLE `customers` CHANGE `phone` `customer_phone` VARCHAR(255)');
        DB::statement('ALTER TABLE `customers` CHANGE `photo` `customer_photo` VARCHAR(255)');
        DB::statement('ALTER TABLE `customers` CHANGE `email` `customer_email` VARCHAR(255)');
        DB::statement('ALTER TABLE `customers` CHANGE `address` `customer_address` VARCHAR(255)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `customers` CHANGE `customer_name` `name`  VARCHAR(255)');
        DB::statement('ALTER TABLE `customers` CHANGE `customer_phone` `phone` VARCHAR(255)');
        DB::statement('ALTER TABLE `customers` CHANGE `customer_photo` `photo` VARCHAR(255)');
        DB::statement('ALTER TABLE `customers` CHANGE `customer_email` `email` VARCHAR(255)');
        DB::statement('ALTER TABLE `customers` CHANGE `customer_address` `address` VARCHAR(255)');
    }
};
