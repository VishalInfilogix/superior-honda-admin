<?php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleCategoryController;
use App\Http\Controllers\VehicleTypeController;
use App\Http\Controllers\VehicleBrandController;
use App\Http\Controllers\VehicleModelController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\RoleAndPermisionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'auth.session'])->group(function () {
    Route::resources([
        'dashboard'     => DashboardController::class,
        'profile'       => ProfileController::class,
        'user-roles'    => UserRoleController::class,
        'vehicle-categories' => VehicleCategoryController::class,
        'vehicle-types' => VehicleTypeController::class,
        'vehicle-brands' => VehicleBrandController::class,
        'vehicle-models' => VehicleModelController::class,
        'roles-and-permissions' => RoleAndPermisionController::class
    ]);
});

Route::get('/', function (){
    return redirect()->route('dashboard.index');
});

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/authenticate', [AuthController::class, 'authenticate'])->name('authenticate');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
