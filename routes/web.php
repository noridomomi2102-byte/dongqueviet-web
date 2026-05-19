<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EvidenceReportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tim-kiem', [SearchController::class, 'index'])->name('search');
Route::get('/danh-muc/{slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/bai-viet/{slug}', [PostController::class, 'show'])->name('post.show');
Route::get('/gui-bao-cao', [EvidenceReportController::class, 'create'])->name('report.create');
Route::post('/gui-bao-cao', [EvidenceReportController::class, 'store'])->name('report.store');
Route::get('/gui-bao-cao/captcha', [EvidenceReportController::class, 'captcha'])->name('report.captcha');
