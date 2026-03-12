<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('original_filename');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('download_limit')->nullable();
            $table->unsignedInteger('total_downloads')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('member_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('download_id')->constrained()->cascadeOnDelete();
            $table->timestamp('downloaded_at');
            $table->string('ip_address', 45)->nullable();

            $table->index(['member_id', 'download_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_downloads');
        Schema::dropIfExists('downloads');
    }
};
