<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
//use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
/*     Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']); */

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

// MVP_POSTERIOR: Recuperacion de contrasena
// MVP_POSTERIOR |     Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
// MVP_POSTERIOR |                 ->name('password.request');
// MVP_POSTERIOR | 
// MVP_POSTERIOR |     Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
// MVP_POSTERIOR |                 ->name('password.email');
// MVP_POSTERIOR | 
// MVP_POSTERIOR |     Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
// MVP_POSTERIOR |                 ->name('password.reset');
// MVP_POSTERIOR | 
// MVP_POSTERIOR |     Route::post('reset-password', [NewPasswordController::class, 'store'])
// MVP_POSTERIOR |                 ->name('password.store');
});

Route::middleware('auth')->group(function () {
// MVP_POSTERIOR: Verificacion y administracion de credenciales
// MVP_POSTERIOR |     Route::get('verify-email', EmailVerificationPromptController::class)
// MVP_POSTERIOR |                 ->name('verification.notice');
// MVP_POSTERIOR | 
// MVP_POSTERIOR |     Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
// MVP_POSTERIOR |                 ->middleware(['signed', 'throttle:6,1'])
// MVP_POSTERIOR |                 ->name('verification.verify');
// MVP_POSTERIOR | 
// MVP_POSTERIOR |     Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
// MVP_POSTERIOR |                 ->middleware('throttle:6,1')
// MVP_POSTERIOR |                 ->name('verification.send');
// MVP_POSTERIOR | 
// MVP_POSTERIOR |     Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
// MVP_POSTERIOR |                 ->name('password.confirm');
// MVP_POSTERIOR | 
// MVP_POSTERIOR |     Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
// MVP_POSTERIOR | 
// MVP_POSTERIOR |     Route::put('password', [PasswordController::class, 'update'])->name('password.update');
// MVP_POSTERIOR | 
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');
});
