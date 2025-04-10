<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('service_providers')->onDelete(action: 'cascade');
            $table->enum('status', ['pending', 'accepted', 'completed', 'cancelled', 'rejected'])->default('pending');
            $table->dateTime('scheduled_at');
            $table->dateTime('end_at')->nullable();
            $table->integer('duration')->nullable(); 
            $table->decimal('price', 10, 2)->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'refunded', 'failed'])->default('pending');
            $table->string('address');
            $table->text('notes')->nullable();
            $table->tinyInteger('rating')->nullable();
            $table->text('review')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();
            $table->index('status');
            $table->index('scheduled_at');
            $table->index('provider_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};