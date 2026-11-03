<?php

use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MotorcycleController as AdminMotorcycleController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\Client\ApplicationController as ClientApplicationController;
use App\Http\Controllers\Staff\ApplicationController as StaffApplicationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'home'])->name('home');
Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalogo/{motorcycle:slug}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/simulacion', [CatalogController::class, 'simulation'])->name('simulation');
Route::get('/requisitos', [CatalogController::class, 'requirements'])->name('requirements');

Route::middleware('guest')->group(function () {
    Route::get('/ingresar', [LoginController::class, 'create'])->name('login');
    Route::post('/ingresar', [LoginController::class, 'store']);
    Route::get('/registro', [RegisterController::class, 'create'])->name('register');
    Route::post('/registro', [RegisterController::class, 'store']);
});

Route::post('/salir', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::middleware('permission:solicitudes.crear')->group(function () {
        Route::get('/mis-solicitudes', [ClientApplicationController::class, 'index'])->name('client.applications.index');
        Route::get('/mis-solicitudes/nueva', [ClientApplicationController::class, 'create'])->name('client.applications.create');
        Route::post('/mis-solicitudes', [ClientApplicationController::class, 'store'])->name('client.applications.store');
        Route::get('/mis-solicitudes/{application}', [ClientApplicationController::class, 'show'])->name('client.applications.show');
        Route::post('/mis-solicitudes/{application}/documentos/{document}', [ClientApplicationController::class, 'replaceDocument'])->name('client.documents.replace');
    });

    Route::middleware('permission:solicitudes.revisar')->group(function () {
        Route::get('/bandeja', [StaffApplicationController::class, 'index'])->name('staff.applications.index');
        Route::get('/bandeja/{application}', [StaffApplicationController::class, 'show'])->name('staff.applications.show');
        Route::post('/bandeja/{application}/documentos/{document}', [StaffApplicationController::class, 'reviewDocument'])->name('staff.documents.review');
        Route::post('/bandeja/{application}/preevaluacion', [StaffApplicationController::class, 'preevaluate'])->name('staff.applications.preevaluate');
        Route::post('/bandeja/{application}/papeles', [StaffApplicationController::class, 'verifyPapers'])->name('staff.applications.papers');
        Route::post('/bandeja/{application}/carpeta', [StaffApplicationController::class, 'receiveFolder'])->name('staff.applications.folder');
        Route::post('/bandeja/{application}/ubicacion', [StaffApplicationController::class, 'verifyLocation'])->name('staff.applications.location');
        Route::post('/bandeja/{application}/rechazar', [StaffApplicationController::class, 'reject'])->name('staff.applications.reject');
    });

    Route::post('/bandeja/{application}/aprobar', [StaffApplicationController::class, 'approve'])
        ->middleware('permission:solicitudes.aprobar')
        ->name('staff.applications.approve');

    Route::middleware('permission:panel.admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::middleware('permission:sedes.gestionar')->group(function () {
            Route::get('/sedes', [BranchController::class, 'index'])->name('branches.index');
            Route::get('/sedes/nueva', [BranchController::class, 'create'])->name('branches.create');
            Route::post('/sedes', [BranchController::class, 'store'])->name('branches.store');
            Route::get('/sedes/{branch}/editar', [BranchController::class, 'edit'])->name('branches.edit');
            Route::put('/sedes/{branch}', [BranchController::class, 'update'])->name('branches.update');
        });

        Route::middleware('permission:usuarios.gestionar')->group(function () {
            Route::get('/usuarios', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('/usuarios/nuevo', [AdminUserController::class, 'create'])->name('users.create');
            Route::post('/usuarios', [AdminUserController::class, 'store'])->name('users.store');
            Route::get('/usuarios/{user}/editar', [AdminUserController::class, 'edit'])->name('users.edit');
            Route::put('/usuarios/{user}', [AdminUserController::class, 'update'])->name('users.update');
        });

        Route::middleware('permission:motos.gestionar')->group(function () {
            Route::get('/motos', [AdminMotorcycleController::class, 'index'])->name('motorcycles.index');
            Route::get('/motos/nueva', [AdminMotorcycleController::class, 'create'])->name('motorcycles.create');
            Route::post('/motos', [AdminMotorcycleController::class, 'store'])->name('motorcycles.store');
            Route::get('/motos/{motorcycle}/editar', [AdminMotorcycleController::class, 'edit'])->name('motorcycles.edit');
            Route::put('/motos/{motorcycle}', [AdminMotorcycleController::class, 'update'])->name('motorcycles.update');
        });

        Route::middleware('permission:roles.gestionar')->group(function () {
            Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
            Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        });
    });
});
