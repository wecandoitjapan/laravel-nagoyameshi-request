<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\Subscribed;
use App\Http\Middleware\NotSubscribed;
use App\Http\Controllers\Admin;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\SubscriptionController;

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

Route::group(['middleware' => 'guest:admin'], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::resource('restaurants', RestaurantController::class)->only(['index', 'show']);

    Route::group(['middleware' => ['auth', 'verified']], function () {
        Route::resource('user', UserController::class)->only(['index', 'edit', 'update']);

        // まだサブスクリプションに加入していないユーザーだけがアクセスできるルート
        Route::group(['middleware' => [NotSubscribed::class]], function () {
            // 有料プラン登録ページを表示する。
            Route::get('subscription/create', [SubscriptionController::class, 'create'])->name('subscription.create');
            // 	ユーザーのサブスクリプションを作成する
            Route::post('subscription', [SubscriptionController::class, 'store'])->name('subscription.store');
        });

        // すでにサブスクリプションに加入しているユーザーだけが利用できるルート
        Route::group(['middleware' => [Subscribed::class]], function () {
            // 加入中プランの編集フォームを表示
            Route::get('subscription/edit', [SubscriptionController::class, 'edit'])->name('subscription.edit');
            // 編集内容（例：プラン変更）を保存
            Route::patch('subscription', [SubscriptionController::class, 'update'])->name('subscription.update');
            // 解約の確認画面などを表示
            Route::get('subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
            // 実際にサブスクリプションを解約
            Route::delete('subscription', [SubscriptionController::class, 'destroy'])->name('subscription.destroy');
        });
    });
});

require __DIR__.'/auth.php';

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth:admin'], function () {
    Route::get('home', [Admin\HomeController::class, 'index'])->name('home');

    Route::resource('users', Admin\UserController::class)->only(['index', 'show']);

    Route::resource('restaurants', Admin\RestaurantController::class);

    Route::resource('categories', Admin\CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::resource('company', Admin\CompanyController::class)->only(['index', 'edit', 'update']);

    Route::resource('terms', Admin\TermController::class)->only(['index', 'edit', 'update']);
});

