<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SitemapController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::group([], function () {
    Route::get('/', [CatalogController::class, 'home'])->name('home');
    Route::get('/sobre', [PageController::class, 'about'])->name('about');
    Route::get('/contato', [PageController::class, 'contact'])->name('contact');
    Route::get('/lancamentos', [LandingController::class, 'launches'])->name('launches');
    Route::get('/linha-premium', [LandingController::class, 'premium'])->name('premium');
    Route::get('/produtos', [ProductController::class, 'index'])->name('products.index');
    Route::get('/produtos/{slug}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/categorias/{slug}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
});

Route::get('/llms.txt', function () {
    $path = public_path('llms.txt');

    abort_unless(file_exists($path), 404);

    return response(file_get_contents($path), 200, [
        'Content-Type'  => 'text/plain; charset=UTF-8',
        'Cache-Control' => 'public, max-age=86400',
        'X-Robots-Tag'  => 'noindex',
    ]);
})->middleware('throttle:20,60')->name('llms');

Route::post('/csp-report', function (Request $request) {
    $payload = $request->input('csp-report', $request->all());

    if (! is_array($payload) || $payload === []) {
        return response()->json(['ok' => true]);
    }

    Log::warning('csp_report_only_violation', [
        'document_uri' => $payload['document-uri'] ?? null,
        'violated_directive' => $payload['violated-directive'] ?? null,
        'effective_directive' => $payload['effective-directive'] ?? null,
        'blocked_uri' => $payload['blocked-uri'] ?? null,
        'source_file' => $payload['source-file'] ?? null,
        'line_number' => $payload['line-number'] ?? null,
        'script_sample' => $payload['script-sample'] ?? null,
    ]);

    return response()->json(['ok' => true]);
})
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->middleware('throttle:600,1')
    ->name('security.csp-report');
