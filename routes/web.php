<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('forms/event-sites', 'pages::forms.event-sites.event-sites-index')->name('event-sites');
    Route::livewire('forms/event-site-detail/{eventSiteId}', 'pages::forms.event-sites.event-site-detail')->name('event-site-detail');
});

require __DIR__ . '/settings.php';
