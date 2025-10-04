<?php

use App\Http\Controllers\Auth\GitHubController;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use Illuminate\Support\Facades\Route;

Route::get('/auth/github/redirect', [GitHubController::class, 'redirect'])->name('auth.github.redirect');
Route::get('/auth/github/callback', [GitHubController::class, 'callback'])->name('auth.github.callback');

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});
