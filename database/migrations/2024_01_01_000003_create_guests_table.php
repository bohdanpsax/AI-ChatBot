<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campsite_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('language')->default('en');
            $table->string('booking_ref')->nullable();
            $table->date('checkin_date')->nullable();
            $table->date('checkout_date')->nullable();
            $table->string('pitch_number')->nullable();
            $table->timestamps();

            $table->index(['campsite_id', 'booking_ref']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};