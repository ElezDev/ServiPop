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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('service_provider_id')->unsigned();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('billing_cycle'); // monthly, yearly, etc.
            $table->text('features');
            $table->timestamps();
        
            $table->foreign('service_provider_id')->references('id')->on('service_providers');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
