<?php

use App\Http\Controllers\AcademyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProtocolController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\PublicacionesController;
use App\Http\Controllers\DatesAndTermsController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MonitoreoController;
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
    Route::get('/datesList', [DatesAndTermsController::class, 'getAllSchoolCyclesAsArray']);
    Route::get('/date', [DatesAndTermsController::class, 'getSchoolCycleData']);
    Route::put('/date', [DatesAndTermsController::class, 'updateSchoolCycle']);
    Route::delete('/date', [DatesAndTermsController::class, 'deleteSchoolCycle']);
    Route::get('/checkUpload', [DatesAndTermsController::class, 'checkIfUploadIsAvailable']);

    // Protocol routes
    Route::post('/createProtocol', [ProtocolController::class, 'createProtocol']);
    Route::get('/getProtocolDoc/{id}', [ProtocolController::class, 'getProtocolDoc']);
    Route::get('/getProDoc/{id}', [ProtocolController::class, 'getProDoc']);
    Route::get('/listProtocols', [ProtocolController::class, 'listProtocols']);
    Route::get('/getQuestionare', [ProtocolController::class, 'getQuestionare']);


    // Academy routes
    Route::get('/academies', [AcademyController::class, 'getAllAcademies']);
});

// Verificar Email
Route::get('/verify-email/{id}', [UsersController::class, 'VerifyMail']);
//Recuperar Contraseña
Route::post('/recuperar-password', [AuthController::class, 'recuperarPassword']);
Route::post('/reset-password/{token}', [AuthController::class, 'resetPassword']);
Route::post('/reset-id/{id}', [AuthController::class, 'resetPasswordID']);

//Recibir correo
Route::post('/buzon', [AuthController::class, 'recibiremail']);

//Ruta del principla publicaciones
Route::post('/avisocrear', [PublicacionesController::class, 'setAvisos']);
Route::get('/aviso', [PublicacionesController::class, 'getAviso']);
Route::get('/aviso/{id}', [PublicacionesController::class, 'getAvisoID']);
Route::put('/aviso/{id}', [PublicacionesController::class, 'updateAviso']);
Route::delete('/aviso/{id}', [PublicacionesController::class, 'deleteAviso']);

Route::post('/tipcrear', [PublicacionesController::class, 'setTip']);
Route::get('/tip', [PublicacionesController::class, 'getTip']);
Route::get('/tip/{id}', [PublicacionesController::class, 'getTipID']);;
Route::put('/tip/{id}', [PublicacionesController::class, 'updateTip']);
Route::delete('/tip/{id}', [PublicacionesController::class, 'deleteTip']);

Route::post('/preguntacrear', [PublicacionesController::class, 'setPregunta']);
Route::get('/pregunta', [PublicacionesController::class, 'getPreguntas']);
Route::get('/pregunta/{id}', [PublicacionesController::class, 'getPreguntaID']);
Route::put('/pregunta/{id}', [PublicacionesController::class, 'updatePregunta']);
Route::delete('/pregunta/{id}', [PublicacionesController::class, 'deletePregunta']);

Route::get('/monitoreo/{id}', [MonitoreoController::class, 'getMonitoreo']);

Route::post('/subir-imagen', [PublicacionesController::class, 'subirImagen']);
Route::get('/ver-carpeta-drive', [PublicacionesController::class, 'verCarpetaDrive']);

Route::get('/clasicar/{id}',[ProtocolController::class, 'getProtocol']);
Route::get('/academias',[UsersController::class, 'getAcademies']);

Route::post('/clasificarProtocolo',[ProtocolController::class, 'clasificarProtocolo']);
Route::post('/selectProtocol',[ProtocolController::class, 'selectProtocol']);
