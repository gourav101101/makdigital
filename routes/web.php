<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Frontend\EnquiryController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/work', [PageController::class, 'work'])->name('work.index');
Route::get('/services', [PageController::class, 'services'])->name('services.index');
Route::get('/insights', [PageController::class, 'insights'])->name('insights.index');
Route::get('/insights/{slug}', [PageController::class, 'article'])->name('insights.show');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
foreach (['about', 'careers', 'privacy-policy', 'terms-and-conditions'] as $page) {
    Route::get('/'.$page, [PageController::class, 'page'])->name($page);
}
Route::get('/sitemap.xml', [PageController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PageController::class, 'robots']);
foreach (config('legacy-redirects') as $source => $target) {
    Route::redirect('/'.$source, $target, 301);
}

Route::get('/work/{slug}', [HomeController::class, 'project'])->name('work.show');
Route::get('/services/{slug}', [HomeController::class, 'service'])->name('services.show');
Route::post('/enquiries', EnquiryController::class)->middleware('throttle:5,1')->name('enquiries.store');

Route::get('/admin/login', [AuthController::class, 'form'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('admin.authenticate');
Route::prefix('admin')->name('admin.')->middleware(EnsureAdmin::class)->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/content', [ContentController::class, 'index'])->name('content.index');
    Route::get('/content/create', [ContentController::class, 'create'])->name('content.create');
    Route::post('/content', [ContentController::class, 'store'])->name('content.store');
    Route::get('/content/{entry}/edit', [ContentController::class, 'edit'])->name('content.edit');
    Route::put('/content/{entry}', [ContentController::class, 'update'])->name('content.update');
    Route::get('/enquiries', [DashboardController::class, 'enquiries'])->name('enquiries.index');
    Route::get('/enquiries/{enquiry}', [DashboardController::class, 'show'])->name('enquiries.show');
    Route::put('/enquiries/{enquiry}', [DashboardController::class, 'update'])->name('enquiries.update');
    Route::post('/enquiries/{enquiry}/retry', [DashboardController::class, 'retry'])->middleware('throttle:3,1')->name('enquiries.retry');
    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
    Route::put('/settings', [DashboardController::class, 'saveSettings'])->name('settings.save');
});
