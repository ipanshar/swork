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
        Schema::create('cashboxes', function (Blueprint $table) {
            $table->id();
            $table->integer('counterparty_id')->default(0);
            $table->integer('invoice_id')->default(0);
            $table->integer('salary_id')->default(0);
            $table->integer('personal_id')->default(0);
            $table->integer('item_id')->default(0);
            $table->float('incoming')->default(0);
            $table->float('expense')->default(0);
            $table->string('description')->nullable();
            $table->integer('user_id');
            $table->integer('cash')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashboxes');
    }
};
