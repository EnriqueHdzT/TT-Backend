<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProtocolController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\DatesAndTermsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/verify-email/{id}', [UsersController::class, 'VerifyMail']);
Route::post('/recuperar-password', [AuthController::class, 'recuperarPassword']);
Route::post('/reset-password/{token}', [AuthController::class, 'resetPassword']);
Route::post('/ayuda', [AuthController::class, 'recibiremail']);

// Protocol routes
Route::get('/addProtocol/{id}', [ProtocolController::class, 'readProtocol']);
Route::get('/addProtocol', [ProtocolController::class, 'readProtocols']);
Route::put('/addProtocol/{id}', [ProtocolController::class, 'updateProtocol']);
Route::delete('/addProtocol/{id}', [ProtocolController::class, 'deleteProtocol']);

// Protected routes
Route::group(['middleware' => ['auth:sanctum', 'update.token.expiry']], function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/keepalive', [AuthController::class, 'keepAlive']);

    // User routes
    Route::get('/users', [UsersController::class, 'getUsers']);
    Route::get('/userId', [UsersController::class, 'getSelfId']);
    Route::get('/user/{id}', [UsersController::class, 'getUserData']);
    Route::delete('/user/{id}', [UsersController::class, 'deleteUser']);
    Route::get('/searchUsers', [UsersController::class, 'searchUsers']);
    Route::post('/createStudent', [UsersController::class, 'createStudent']);
    Route::post('/createStaff', [UsersController::class, 'createStaff']);
    Route::put('/user', [UsersController::class, 'updateUserData']);
    Route::get('/userExists/{email}', [UsersController::class, 'doesUserExists']);
    Route::get('/selfEmail', [UsersController::class, 'getSelfEmail']);

    // Dates routes
    Route::post('/dates', [DatesAndTermsController::class, 'createSchoolCycle']);
    Route::get('/dates', [DatesAndTermsController::class, 'getAllSchoolCycles']);
    Route::get('/date', [DatesAndTermsController::class, 'getSchoolCycleData']);
    Route::put('/date', [DatesAndTermsController::class, 'updateSchoolCycle']);
    Route::delete('/date', [DatesAndTermsController::class, 'deleteSchoolCycle']);
    Route::get('/checkUpload', [DatesAndTermsController::class, 'checkIfUploadIsAvailable']);

    // Protocol routes
    Route::post('/createProtocol', [ProtocolController::class, 'createProtocol']);
    Route::get('/getProtocolDoc/{id}', [ProtocolController::class, 'getProtocolDoc']);
    Route::get('/listProtocols', [ProtocolController::class, 'listProtocols']);
});
