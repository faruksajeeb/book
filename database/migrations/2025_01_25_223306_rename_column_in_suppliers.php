<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `suppliers` CHANGE `name` `supplier_name` VARCHAR(255)');
        DB::statement('ALTER TABLE `suppliers` CHANGE `phone` `supplier_phone` VARCHAR(255)');
        DB::statement('ALTER TABLE `suppliers` CHANGE `photo` `supplier_photo` VARCHAR(255)');
        DB::statement('ALTER TABLE `suppliers` CHANGE `email` `supplier_email` VARCHAR(255)');
        DB::statement('ALTER TABLE `suppliers` CHANGE `address` `supplier_address` VARCHAR(255)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `suppliers` CHANGE `supplier_name` `name`  VARCHAR(255)');
        DB::statement('ALTER TABLE `suppliers` CHANGE `supplier_phone` `phone` VARCHAR(255)');
        DB::statement('ALTER TABLE `suppliers` CHANGE `supplier_photo` `photo` VARCHAR(255)');
        DB::statement('ALTER TABLE `suppliers` CHANGE `supplier_email` `email` VARCHAR(255)');
        DB::statement('ALTER TABLE `suppliers` CHANGE `supplier_address` `address` VARCHAR(255)');
    }
};
