<?php

use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Protected routes (require auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/notifications/{id}/read', function ($id) {
        $notification = request()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back();
    })->name('notifications.read');

    Route::controller(ProjectController::class)->group(function () {
        Route::get('/projects', 'index');
        Route::get('/projects/{project}/tickets', 'tickets');
    });

    Route::controller(TicketController::class)->group(function () {
        Route::get('/tickets', 'index');
        Route::get('/tickets/{ticket}', 'show')->can('view', 'ticket');
        Route::post('/tickets/{ticket}/close', 'close')->can('close', 'ticket');
    });
});
