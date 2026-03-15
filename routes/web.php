<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\EventSessionController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Public\RegisterAccessController;
use App\Http\Controllers\Public\RegistrationWizardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->to('/login');
});

Route::redirect('/access', '/login');
Route::redirect('/register/access', '/login');

Route::get('/login', [RegisterAccessController::class, 'show']);
Route::post('/login', [RegisterAccessController::class, 'store']);
Route::post('/register/logout', [RegisterAccessController::class, 'destroy']);

Route::middleware(['guest.authed'])->group(function () {
    Route::get('/register', [RegistrationWizardController::class, 'step1']);
    Route::post('/register/step1', [RegistrationWizardController::class, 'postStep1']);

    Route::get('/register/step2', [RegistrationWizardController::class, 'step2']);
    Route::post('/register/step2', [RegistrationWizardController::class, 'postStep2']);

    Route::get('/register/step3', [RegistrationWizardController::class, 'step3']);
    Route::post('/register/step3', [RegistrationWizardController::class, 'postStep3']);

    Route::get('/register/step4', [RegistrationWizardController::class, 'step4']);
    Route::post('/register/submit', [RegistrationWizardController::class, 'submit']);

    Route::get('/register/success/{id}', [RegistrationWizardController::class, 'success'])
        ->whereNumber('id');
});

Route::get('/admin/login', [AdminAuthController::class, 'show']);
Route::post('/admin/login', [AdminAuthController::class, 'store']);
Route::post('/admin/logout', [AdminAuthController::class, 'destroy']);

Route::middleware(['admin.authed'])->group(function () {
    Route::get('/admin', function () {
        return redirect()->to('/admin/registrations');
    });

    Route::get('/admin/registrations', [RegistrationController::class, 'index']);
    Route::get('/admin/registrations/export.csv', [RegistrationController::class, 'exportCsv']);
    Route::get('/admin/registrations/export.xls', [RegistrationController::class, 'exportXls']);
    Route::get('/admin/registrations/{registration}/edit', [RegistrationController::class, 'edit']);
    Route::put('/admin/registrations/{registration}', [RegistrationController::class, 'update']);
    Route::post('/admin/registrations/{registration}/cancel', [RegistrationController::class, 'cancel']);
    Route::delete('/admin/registrations/{registration}', [RegistrationController::class, 'destroy']);

    Route::get('/admin/sessions', [EventSessionController::class, 'index']);
    Route::get('/admin/sessions/create', [EventSessionController::class, 'create']);
    Route::post('/admin/sessions', [EventSessionController::class, 'store']);
    Route::get('/admin/sessions/{session}/edit', [EventSessionController::class, 'edit']);
    Route::put('/admin/sessions/{session}', [EventSessionController::class, 'update']);
    Route::delete('/admin/sessions/{session}', [EventSessionController::class, 'destroy']);
    Route::delete('/admin/sessions/bulk-destroy', [EventSessionController::class, 'destroyMultiple']);
    Route::post('/admin/sessions/{session}/toggle-block', [EventSessionController::class, 'toggleBlock']);
});
