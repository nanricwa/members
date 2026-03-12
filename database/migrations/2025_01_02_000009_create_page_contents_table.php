<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('text');
            $table->text('body')->nullable();
            // 動画用
            $table->string('video_url')->nullable();
            $table->string('video_provider')->nullable();
            // ダウンロード用
            $table->foreignId('download_id')->nullable()->constrained()->nullOnDelete();
            //
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['page_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_contents');
    }
};
