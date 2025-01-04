<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to rename columns for older MySQL versions
        DB::statement('ALTER TABLE `authors` CHANGE `email` `author_email` VARCHAR(255)');
        DB::statement('ALTER TABLE `authors` CHANGE `phone` `author_phone` VARCHAR(255)');
        DB::statement('ALTER TABLE `authors` CHANGE `address` `author_address` TEXT');
        DB::statement('ALTER TABLE `authors` CHANGE `photo` `author_photo` VARCHAR(255)');
        DB::statement('ALTER TABLE `authors` CHANGE `country` `author_country` VARCHAR(255)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the column renaming
        DB::statement('ALTER TABLE `authors` CHANGE `author_email` `email` VARCHAR(255)');
        DB::statement('ALTER TABLE `authors` CHANGE `author_phone` `phone` VARCHAR(255)');
        DB::statement('ALTER TABLE `authors` CHANGE `author_address` `address` TEXT');
        DB::statement('ALTER TABLE `authors` CHANGE `author_photo` `photo` VARCHAR(255)');
        DB::statement('ALTER TABLE `authors` CHANGE `author_country` `country` VARCHAR(255)');
    }
};
