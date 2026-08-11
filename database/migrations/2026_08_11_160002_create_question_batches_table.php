<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('document_id')->nullable()->constrained('documents')->onDelete('set null');
            $table->string('knowledge_area');
            $table->string('difficulty')->nullable();
            $table->integer('requested_count');
            $table->integer('generated_count')->default(0);
            $table->enum('status', ['processing', 'completed', 'partial', 'failed'])->default('processing');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_batches');
    }
};
