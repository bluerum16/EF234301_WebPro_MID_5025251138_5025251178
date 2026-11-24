<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('books_id')->constrained('books')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['users_id', 'books_id']);
        });
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('books_id')->constrained('books')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('comment', 1000);
            $table->timestamps();
            $table->unique(['users_id', 'books_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('favorites');
    }
};
