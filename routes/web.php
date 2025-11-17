<?php

use App\Http\Controllers\MessageController; 
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Broadcast::routes(['middleware' => ['auth']]);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');




Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('/chat', function () {
    return view('chat');
});


Route::middleware('auth')->group(function () {
    Route::get('/message', [MessageController::class, 'index']);
    Route::get('/messages/{userId}', [MessageController::class, 'getMessages']);
    Route::post('/send-message', [MessageController::class, 'sendMessage']);

    Route::post('/mark-as-read', [MessageController::class, 'markAsRead']);

    ///user typing status route
    Route::post('/typing-status', function() {
        event(new \App\Events\TypingEvent(
            auth()->id(),
            request()->receiver_id,
            request()->is_typing
        ));

        return response()->json(['status' => 'ok']);
    });

});


require __DIR__.'/auth.php';
