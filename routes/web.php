<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'home'])->name('home');
Route::get('/sobre', [PageController::class, 'about'])->name('about');
Route::get('/contato', [PageController::class, 'contact'])->name('contact');
Route::get('/lancamentos', [LandingController::class, 'launches'])->name('launches');
Route::get('/linha-premium', [LandingController::class, 'premium'])->name('premium');
Route::get('/produtos', [ProductController::class, 'index'])->name('products.index');
Route::get('/produtos/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/categorias/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
