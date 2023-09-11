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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('customer_id')->unsigned();
            $table->date('sale_date');
            $table->double('total_amount',10,2);
            $table->decimal('discount_percentage',5,2);
            $table->float('discount_amount',8,2);
            $table->decimal('vat_percentage',5,2);
            $table->float('vat_amount',8,2);
            $table->double('net_amount',10,2);
            $table->double('pay_amount',10,2);
            $table->double('due_amount',10,2);
            $table->string('paid_by');
            $table->string('file')->comment('If any invoice want to attached');
            $table->dateTimeTz('created_at', $precision = 0);
            $table->dateTimeTz('updated_at', $precision = 0);
            $table->softDeletesTz('deleted_at', $precision = 0);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
