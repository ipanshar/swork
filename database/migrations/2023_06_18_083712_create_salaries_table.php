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
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->integer('personal_id');
            $table->float('accrued')->default(0);
            $table->float('held')->default(0);
            $table->float('paid')->default(0);
            $table->float('balance')->default(0);
            $table->string('description')->nullable();
            $table->integer('user_id');
            $table->integer('partner')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
