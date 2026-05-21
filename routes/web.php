<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'views::dashboard')->name('dashboard');
    Route::livewire('forms/event-sites', 'pages::forms.event-sites.event-sites-index')->name('event-sites');
    Route::livewire('forms/event-site-detail/{eventSiteId}', 'pages::forms.event-sites.event-site-detail')->name('event-site-detail');
    Route::livewire('forms/churches', 'pages::forms.churches.churches-index')->name('churches');
    Route::livewire('forms/persons', 'pages::forms.persons.persons-index')->name('persons');

    Route::livewire('events', 'pages::events.events-index')->name('events');
    Route::livewire('events/event-detail/{eventId}', 'pages::events.event-detail')->name('event-detail');
    Route::livewire('events/event-detail/{eventId}/participant/{allocationId}', 'pages::events.participants.participant-detail')->name('participant-detail');
    Route::livewire('events/event-detail/{eventId}/service/{serviceId}', 'pages::events.services.service-detail')->name('service-detail');
    Route::livewire('events/event-detail/{eventId}/trip/{tripId}', 'pages::events.trips.trip-detail')->name('trip-detail');
    Route::get('exports/services', [\App\Http\Controllers\Exports\ServiceExportController::class, 'index'])->name('exports.services');
    Route::get('exports/trips', [\App\Http\Controllers\Exports\TripExportController::class, 'index'])->name('exports.trips');
    Route::get('exports/allocations', [\App\Http\Controllers\Exports\AllocationExportController::class, 'index'])->name('exports.allocations');
});

require __DIR__ . '/settings.php';
