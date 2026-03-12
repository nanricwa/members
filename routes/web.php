<?php

use App\Http\Controllers\Member\AuthController;
use App\Http\Controllers\Member\DashboardController;
use App\Http\Controllers\Member\DownloadController;
use App\Http\Controllers\Member\PageController;
use App\Http\Controllers\Member\ProfileController;
use App\Http\Controllers\Member\RegistrationController;
use App\Http\Controllers\Member\SubscriptionController;
use App\Http\Controllers\Payment\PaymentController;
use Illuminate\Support\Facades\Route;

// トップページ → ログインへリダイレクト
Route::get('/', function () {
    return redirect('/member/login');
});

// 公開: 登録フォーム
Route::get('/register/{form:slug}', [RegistrationController::class, 'show'])->name('registration.show');
Route::post('/register/{form:slug}', [RegistrationController::class, 'store'])->name('registration.store');
Route::get('/register/{form:slug}/complete', [RegistrationController::class, 'complete'])->name('registration.complete');

// 決済結果ページ
Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');

// 会員認証
Route::prefix('member')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('member.login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('member.logout');
});

// 会員マイページ (要認証)
Route::prefix('mypage')->middleware('auth:member')->group(function () {
    Route::get('/', DashboardController::class)->name('member.dashboard');
    Route::get('/category/{category:slug}', [PageController::class, 'showCategory'])->name('member.category');
    Route::get('/page/{page:slug}', [PageController::class, 'showPage'])->name('member.page');
    Route::get('/download/{download}', DownloadController::class)->name('member.download');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('member.profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('member.profile.update');

    // サブスクリプション管理
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('member.subscriptions');
    Route::post('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('member.subscriptions.cancel');
});
