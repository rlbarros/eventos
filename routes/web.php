<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('event-sites', 'pages::forms.event-sites-index')->name('event-sites');
});

require __DIR__ . '/settings.php';
