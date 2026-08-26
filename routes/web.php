<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ShopController::class, 'index'])->name('shop.index');
Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::get('categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::patch('categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::patch('categories/{category}/toggle', [AdminCategoryController::class, 'toggle'])->name('categories.toggle');
        Route::delete('categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
        Route::get('settings/logo', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings/logo', [SettingController::class, 'updateLogo'])->name('settings.logo');
        Route::delete('settings/logo', [SettingController::class, 'removeLogo'])->name('settings.logo.remove');
        Route::get('contacts', [AdminContactMessageController::class, 'index'])->name('contacts.index');
        Route::delete('contacts/{contactMessage}', [AdminContactMessageController::class, 'destroy'])->name('contacts.destroy');
    });
});
