<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('twilio_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('phone_number');
            $table->string('twilio_sid')->nullable();
            $table->string('twilio_token')->nullable();
            $table->boolean('is_whatsapp')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('phone_number');
            $table->index('is_active');
        });

        // Add twilio_number_id to sms_messages to track which number sent/received
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('twilio_number_id')->nullable()->after('twilio_sid');
            $table->foreign('twilio_number_id')->references('id')->on('twilio_numbers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->dropForeign(['twilio_number_id']);
            $table->dropColumn('twilio_number_id');
        });

        Schema::dropIfExists('twilio_numbers');
    }
};
