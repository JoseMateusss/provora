<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('question_batch_id')->constrained('question_batches')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->text('statement');
            $table->json('alternatives');
            $table->char('correct_alternative', 1);
            $table->text('explanation')->nullable();
            $table->enum('difficulty', ['facil', 'medio', 'dificil'])->default('medio');
            $table->enum('status', ['draft', 'edited', 'approved', 'deleted'])->default('draft');
            $table->integer('order')->default(1);
            $table->timestamps();

            $table->index('question_batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
