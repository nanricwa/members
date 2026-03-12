<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('email_type'); // registration_complete/newsletter/automation
            $table->string('subject');
            $table->text('body_preview')->nullable();
            $table->string('status')->default('queued'); // queued/sent/failed
            $table->string('related_type')->nullable(); // newsletter/automation_task/registration_form
            $table->unsignedBigInteger('related_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'email_type']);
            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
