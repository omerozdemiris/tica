<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Frontend\AuthController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\UserController;
use App\Http\Controllers\Frontend\ReturnController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\NotificationController;
use App\Http\Controllers\Frontend\ProductCommentController;
use App\Http\Controllers\LinkController;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/{hash}', [LinkController::class, 'handle'])->where('hash', '[a-zA-Z0-9]{6}');
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/{id}/{slug?}', [BlogController::class, 'show'])->name('show');
});
Route::get('/email/verify', [VerifyEmailController::class, 'notice'])->name('verification.notice');
Route::post('/email/verify/resend', [VerifyEmailController::class, 'resend'])->name('verification.resend');
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/{product}/{slug?}', [ProductController::class, 'show'])->name('show');
});

Route::prefix('categories')->name('categories.')->group(function () {
    Route::get('/', [ProductController::class, 'categories'])->name('index');
    Route::get('/{category}/{slug?}', [ProductController::class, 'byCategory'])->name('show');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password/search', [AuthController::class, 'searchUserByEmail'])->name('password.search');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/', [CartController::class, 'store'])->name('store');
    Route::patch('/{item}', [CartController::class, 'update'])->name('update');
    Route::delete('/{item}', [CartController::class, 'destroy'])->name('destroy');
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');
    Route::post('/apply-promotion', [CartController::class, 'applyPromotion'])->name('apply-promotion');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/start', [CheckoutController::class, 'start'])->name('checkout.start');
    Route::get('/checkout/payment/{merchantOid}', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/checkout/callback', [CheckoutController::class, 'callback'])->name('checkout.callback');
    Route::match(['get', 'post'], '/checkout/ziraat/callback', [CheckoutController::class, 'ziraatCallback'])->name('checkout.ziraat.callback');
    Route::match(['get', 'post'], '/checkout/iyzico/callback', [CheckoutController::class, 'iyzicoCallback'])->name('checkout.iyzico.callback');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/fail', [CheckoutController::class, 'fail'])->name('checkout.fail');
    Route::get('/checkout/wire/success/{orderNumber}', [CheckoutController::class, 'wireSuccess'])->name('checkout.wire.success');
    Route::get('/checkout/wire/error', [CheckoutController::class, 'wireError'])->name('checkout.wire.error');
});

Route::prefix('returns')->name('returns.')->group(function () {
    Route::get('/', [ReturnController::class, 'lookup'])->name('lookup');
    Route::post('/', [ReturnController::class, 'store'])->name('store');
});

Route::middleware('auth')->group(function () {
    Route::get('/returns/order/{order}', [ReturnController::class, 'createFromOrder'])->name('returns.order');
});

Route::middleware(['auth', 'verified'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [UserController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [UserController::class, 'orderShow'])->name('orders.show');
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::post('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::get('/addresses', [UserController::class, 'addresses'])->name('addresses');
    Route::post('/addresses', [UserController::class, 'storeAddress'])->name('addresses.store');

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/latest', [NotificationController::class, 'getLatest'])->name('latest');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::post('/clear-all', [NotificationController::class, 'clearAll'])->name('clear-all');
        Route::get('/{id}', [NotificationController::class, 'show'])->name('show');
    });

    Route::prefix('comments')->name('comments.')->group(function () {
        Route::get('/comments', [ProductCommentController::class, 'index'])->name('index');
        Route::get('/comment/{comment}', [ProductCommentController::class, 'show'])->name('show');
        Route::post('/{product}', [ProductCommentController::class, 'store'])->name('store');
    });
});

Route::get('/about', [PageController::class, 'about'])->name('pages.about');
Route::get('/privacy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/cookies', [PageController::class, 'cookies'])->name('pages.cookies');
Route::get('/distance-selling', [PageController::class, 'distanceSelling'])->name('pages.distance-selling');
