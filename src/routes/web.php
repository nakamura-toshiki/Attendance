<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Requests\EmailVerificationRequest;
use Illuminate\Http\Request;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware('auth','verified')->group(function (){
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('user.index');
    Route::post('/attendance', [AttendanceController::class, 'attendance'])->name('user.attendance');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('user.list');
    Route::post('/attendance/{attendance_id}', [AttendanceController::class, 'showEdit'])->name('user.edit');
});

Route::middleware(['auth:web,admin'])->group(function () {
    Route::get('/attendance/{attendance_id}', [AttendanceController::class, 'show'])->name('user.show');
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'request'])->name('user.request');
});

Route::prefix('admin')->middleware('guest:admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminLoginController::class, 'login']);
});

Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/attendance/list', [AdminController::class, 'list'])->name('admin.list');
    Route::get('/admin/staff/list', [AdminController::class, 'staffList'])->name('admin.staff');
    Route::post('/admin/attendance/{attendance_id}', [AdminController::class, 'showEdit'])->name('admin.edit');
    Route::get('/admin/attendance/staff/{user_id}', [AdminController::class, 'person'])->name('admin.person');
    Route::get('/admin/attendance/staff/{user_id}/export', [AdminController::class, 'exportCsv'])->name('admin.export');
    Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AdminController::class, 'showRequest'])->name('admin.showRequest');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [AdminController::class, 'approve'])->name('admin.approve');
});

Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('email');
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->name('verification.notice');

Route::post('/email/verification-notification', function (Request $request) {
    session()->get('unauthenticated_user')->sendEmailVerificationNotification();
    session()->put('resent', true);
    return back()->with('message', 'Verification link sent!');
})->name('verification.send');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    session()->forget('unauthenticated_user');
    return redirect('/attendance');
})->name('verification.verify');