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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('borrowing_method', 20);
            $table->date('borrow_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->string('status', 20);
            $table->timestamps();
            $table->foreignId('reservations_id')->nullable()->unique()->constrained('reservations')->restrictOnDelete();
            $table->foreignId('users_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('books_id')->constrained('books')->restrictOnDelete();
            $table->foreignId('librarian_id')->constrained('users')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
