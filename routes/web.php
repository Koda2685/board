<?php

use App\Http\Controllers\GovernanceDocumentController;
use App\Http\Controllers\GovernanceRecordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminGovernanceViewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardGovernanceEntryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/dashboard/governance-documents/reference', [DashboardGovernanceEntryController::class, 'referencePreview'])
        ->name('dashboard.governance-documents.reference');

    Route::post('/dashboard/governance-documents', [DashboardGovernanceEntryController::class, 'storeDocument'])
        ->name('dashboard.governance-documents.store');

    Route::post('/dashboard/governance-records', [DashboardGovernanceEntryController::class, 'storeRecord'])
        ->name('dashboard.governance-records.store');

    Route::get('/governance-documents/{governanceDocument}/download', [AdminGovernanceViewController::class, 'download'])
        ->name('admin.governance-documents.download');

    Route::get('/governance-documents/{governanceDocument}/viewer', [AdminGovernanceViewController::class, 'viewer'])
        ->name('admin.governance-documents.viewer');
});

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [\App\Http\Controllers\AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users', [\App\Http\Controllers\AdminUserController::class, 'store'])->name('users.store');
        Route::get('/governance-documents', [AdminGovernanceViewController::class, 'index'])
            ->name('governance-documents.index');
        Route::get('/governance-documents/{governanceDocument}', [AdminGovernanceViewController::class, 'show'])
            ->name('governance-documents.show');
        Route::put('/governance-documents/{governanceDocument}', [AdminGovernanceViewController::class, 'update'])
            ->name('governance-documents.update');
    });

Route::middleware('auth')->group(function () {
    Route::apiResource('governance-documents', GovernanceDocumentController::class);
    Route::apiResource('governance-documents.governance-records', GovernanceRecordController::class)
        ->shallow();

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
