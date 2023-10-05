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
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->integer('organization_id');
            $table->integer('subject_id')->nullable()->default(null);
            $table->integer('subject_count')->nullable()->default(null);
            $table->integer('service_id');
            $table->float('service_price');
            $table->integer('service_count');
            $table->float('total_sum');
            $table->string('coment')->nullable()->default(null);
            $table->date('public_date');
            $table->integer('invoice_id')->default(0);
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
