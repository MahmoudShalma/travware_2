<?php

use App\Helpers\API\ResponseBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', [AuthController::class,'register']);
Route::post('/login', [AuthController::class,'login']);
Route::get('/exception',function (Request $request) {
    return ResponseBuilder::response(null, "you need to login first.", ["you need to login first."], ResponseBuilder::Success);
})->name("exception");
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts/store', [PostController::class,'store']);
    Route::post('/posts/{post}/upload-image', 'PostController@uploadImage');
    Route::get('/posts', [PostController::class,'index']);
    Route::get('/post', [PostController::class,'show']);

});
