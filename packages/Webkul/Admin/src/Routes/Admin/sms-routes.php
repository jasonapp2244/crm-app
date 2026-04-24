<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\SMS\SMSController;
use Webkul\Admin\Http\Controllers\SMS\TemplateController;
use Webkul\Admin\Http\Controllers\SMS\TwilioNumberController;

Route::prefix('sms')->group(function () {
    // Message routes
    Route::controller(SMSController::class)->group(function () {
        Route::get('', 'index')->name('admin.sms.index');

        Route::post('send', 'store')->name('admin.sms.store');

        Route::get('stats', 'stats')->name('admin.sms.stats');

        Route::get('conversation/{personId}', 'conversation')->name('admin.sms.conversation');

        Route::get('conversation/{personId}/poll', 'conversationPoll')->name('admin.sms.conversation.poll');

        Route::get('message/{id}', 'view')->name('admin.sms.view');

        Route::delete('message/{id}', 'destroy')->name('admin.sms.delete');

        Route::post('inbound-webhook', 'inboundWebhook')
            ->name('admin.sms.inbound_webhook')
            ->withoutMiddleware(['user', 'auth']);
    });

    // Twilio Numbers management routes
    Route::prefix('numbers')->controller(TwilioNumberController::class)->group(function () {
        Route::get('', 'index')->name('admin.sms.numbers.index');

        Route::post('', 'store')->name('admin.sms.numbers.store');

        Route::get('active', 'activeNumbers')->name('admin.sms.numbers.active');

        Route::get('{id}', 'edit')->name('admin.sms.numbers.edit');

        Route::put('{id}', 'update')->name('admin.sms.numbers.update');

        Route::delete('{id}', 'destroy')->name('admin.sms.numbers.delete');
    });

    // Templates management routes
    Route::prefix('templates')->controller(TemplateController::class)->group(function () {
        Route::get('', 'index')->name('admin.sms.templates.index');

        Route::post('', 'store')->name('admin.sms.templates.store');

        Route::get('active', 'active')->name('admin.sms.templates.active');

        Route::get('{id}', 'edit')->name('admin.sms.templates.edit');

        Route::put('{id}', 'update')->name('admin.sms.templates.update');

        Route::delete('{id}', 'destroy')->name('admin.sms.templates.delete');
    });
});
