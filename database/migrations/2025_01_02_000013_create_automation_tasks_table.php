<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trigger_type');
            $table->json('trigger_value')->nullable();
            $table->string('action_type');
            $table->json('action_value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_tasks');
    }
};
