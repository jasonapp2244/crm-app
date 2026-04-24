<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->timestamp('scheduled_at')->nullable()->after('error_message');
            $table->unsignedBigInteger('template_id')->nullable()->after('scheduled_at');

            $table->foreign('template_id')->references('id')->on('sms_templates')->onDelete('set null');
            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropIndex(['status', 'scheduled_at']);
            $table->dropColumn(['scheduled_at', 'template_id']);
        });
    }
};
