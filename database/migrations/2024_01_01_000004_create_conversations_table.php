<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campsite_id')->constrained()->onDelete('cascade');
            $table->foreignId('guest_id')->nullable()->constrained()->onDelete('set null');
            $table->string('session_id')->unique();
            $table->string('detected_language')->default('en');
            $table->enum('status', ['active', 'closed', 'escalated'])->default('active');
            $table->integer('message_count')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->index(['campsite_id', 'status']);
            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};