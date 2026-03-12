<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('free');
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            // レイアウト設定
            $table->string('header_image')->nullable();
            $table->text('body_html')->nullable();
            $table->string('button_text')->default('登録する');
            $table->text('custom_css')->nullable();
            $table->text('thanks_message')->nullable();
            $table->string('redirect_url')->nullable();
            // 制限設定
            $table->unsignedInteger('capacity')->nullable();
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            // 決済設定 (Phase 2)
            $table->string('payment_gateway')->default('none');
            $table->decimal('amount', 10, 0)->default(0);
            $table->unsignedInteger('trial_days')->default(0);
            // メール設定
            $table->string('completion_email_subject')->nullable();
            $table->text('completion_email_body')->nullable();
            //
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('form_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custom_field_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->unique(['registration_form_id', 'custom_field_id'], 'form_field_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_custom_fields');
        Schema::dropIfExists('registration_forms');
    }
};
