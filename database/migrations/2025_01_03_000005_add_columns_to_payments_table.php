<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('registration_form_id')->nullable()->after('plan_id')
                ->constrained('registration_forms')->nullOnDelete();
            $table->string('description')->nullable()->after('registration_form_id');
            $table->json('metadata')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['registration_form_id']);
            $table->dropColumn(['registration_form_id', 'description', 'metadata']);
        });
    }
};
