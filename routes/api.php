<?php

use App\Http\Controllers\Api\SmsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.sms-client')->prefix('v1')->group(function () {
    Route::post('/sms/send', [SmsController::class, 'send']);
});
