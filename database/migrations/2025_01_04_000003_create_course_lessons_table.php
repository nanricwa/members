<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_module_id')->constrained()->cascadeOnDelete();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->timestamps();

            $table->unique(['course_module_id', 'page_id']);
            $table->index(['course_module_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_lessons');
    }
};
