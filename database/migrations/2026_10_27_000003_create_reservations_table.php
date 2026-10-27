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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('status', 30);
            $table->timestamp('pickup_deadline')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->foreignId('users_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('books_id')->constrained('books')->restrictOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
