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
        Schema::create('merchandises', function (Blueprint $table) {
            $table->id();
            $table->integer('organization_id');
            $table->integer('service_id');
            $table->integer('service_count');
            $table->float('price')->default(0);
            $table->float('rate')->default(0);
            $table->float('accrued')->default(0);
            $table->integer('salary_id')->default(0);
            $table->integer('status_id')->default(1);
            $table->integer('user_id');
            $table->timestamps();
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchandises');
    }
};
