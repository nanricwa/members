<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_lesson_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();

            $table->unique(['member_id', 'course_lesson_id']);
            $table->index(['member_id', 'is_completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_lesson_progress');
    }
};
