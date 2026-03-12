<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_task_id')->constrained('automation_tasks')->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action_type');
            $table->json('action_detail')->nullable();
            $table->string('status')->default('success'); // success/failed/skipped
            $table->text('error_message')->nullable();
            $table->timestamp('executed_at');
            $table->timestamps();

            $table->index(['automation_task_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_logs');
    }
};
