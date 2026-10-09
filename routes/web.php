<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/signing', function () {
//    signing
    return response()->json([
        'url' => URL::signedRoute('email.action', ['user' => 123])
    ]);
});

Route::get('/action', function (Request $request) {
    if (!$request->hasValidSignature()) {
        abort(403);
    }

    // safe to proceed
    return response()->json([
        'message' => 'Signed route found'
    ]);

})->name('email.action');



use App\Http\Controllers\Hospital\HospitalInquiryController;
use App\Http\Controllers\Hospital\HospitalDeskController;

// CareDesk: Hospital Customer Service & Inquiries Portal
Route::prefix('hospital')->name('hospital.')->group(function () {
    // Public Patient Portal
    Route::get('/', [HospitalInquiryController::class, 'index'])->name('home');
    Route::get('/inquiry/new', [HospitalInquiryController::class, 'create'])->name('inquiry.create');
    Route::post('/inquiry', [HospitalInquiryController::class, 'store'])->name('inquiry.store');
    Route::get('/track', [HospitalInquiryController::class, 'track'])->name('track');
    Route::get('/departments', [HospitalInquiryController::class, 'departments'])->name('departments');
    Route::get('/faq', [HospitalInquiryController::class, 'faq'])->name('faq');

    // Customer Service Staff Desk
    Route::prefix('desk')->name('desk.')->group(function () {
        Route::get('/', [HospitalDeskController::class, 'index'])->name('index');
        Route::get('/ticket/{ticket_code}', [HospitalDeskController::class, 'show'])->name('show');
        Route::post('/ticket/{ticket_code}/respond', [HospitalDeskController::class, 'respond'])->name('respond');
        Route::post('/ticket/{ticket_code}/status', [HospitalDeskController::class, 'updateStatus'])->name('status');
    });
});