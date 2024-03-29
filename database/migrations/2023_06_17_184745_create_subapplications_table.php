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
        Schema::create('subapplications', function (Blueprint $table) {
            $table->id();
            $table->integer('application_id');
            $table->integer('organization_id');
            $table->integer('service_id');
            $table->integer('article_num')->default(0);
            $table->integer('service_num')->default(1);
            $table->integer('service_total')->default(0);
            $table->float('rate')->default(0);
            $table->integer('status_id')->default(1);
            $table->string('description')->nullable();
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subapplications');
    }
};
