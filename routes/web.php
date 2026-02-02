<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TicketController;
use App\Models\Ticket;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('dashboard', 'index')->name('dashboard');
    });

    Route::controller(TicketController::class)->group(function () {
        Route::get('/tickets', 'index')->name('tickets.index');
        Route::get('/tickets/create', 'create')->name('tickets.create')->can('create', Ticket::class);
        Route::post('/tickets', 'store')->name('tickets.store')->can('create', Ticket::class);
        Route::get('/tickets/{ticket}', 'show')->name('tickets.show')->can('view', 'ticket');
    });

    Route::controller(TeamController::class)->group(function () {
        Route::get('/team', 'index')->name('team.index');
    });

    Route::controller(ProjectController::class)->group(function () {
        Route::get('/projects', 'index')->name('projects.index');
        Route::get('/projects/{project}', 'show')->name('projects.show')->can('view', 'project');
    });
});

require __DIR__.'/settings.php';
