<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->string('from')->nullable();
            $table->string('to');
            $table->text('body');
            $table->enum('direction', ['outbound', 'inbound'])->default('outbound');
            $table->string('status')->default('queued');
            $table->enum('channel', ['sms', 'whatsapp'])->default('sms');
            $table->string('twilio_sid')->nullable()->index();
            $table->string('error_message')->nullable();

            $table->unsignedInteger('person_id')->nullable();
            $table->unsignedInteger('lead_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();

            $table->foreign('person_id')->references('id')->on('persons')->onDelete('set null');
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();

            $table->index(['direction', 'created_at']);
            $table->index(['person_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
    }
};
