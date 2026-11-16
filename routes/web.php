
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SetLocale;

Route::redirect('/', '/en');

Route::get('/{locale}', function (string $locale) {
    return view('home');
})
    ->whereIn('locale', ['en', 'id'])
    ->middleware(SetLocale::class)
    ->name('home');

// ATCS Subang Case Study
Route::get('/{locale}/projects/atcs-subang', function (string $locale) {
    return view('projects.atcs-subang');
})
    ->whereIn('locale', ['en', 'id'])
    ->middleware(SetLocale::class)
    ->name('projects.atcs-subang');
