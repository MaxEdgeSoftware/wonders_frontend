<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\FeebackController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UploadsController;
use App\Http\Controllers\UserController;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'XssSanitizer'], function () {
    Route::group(['middleware' => 'api', 'prefix' => 'v1'], function ($router) {
        Route::controller(AuthController::class)->group(function() {
            Route::post('/register', 'register');
            Route::post('/login', 'login');
            Route::post('/forgot-password', 'forgotPassword');
            Route::post('reset-password', 'resetPassword');
            Route::post('resend-verification', 'resendVerificationEmail');
            Route::get('reset-password/{token}', 'resetToken');
            Route::post('verify-account', 'verifyAccount');
            Route::middleware(['verified', 'jwt.verify'])->group(function () {
                Route::post('/logout', 'logout');
                Route::post('/user/change-password', 'changePassword');
            });
        });
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/latest', [ProductController::class, 'newProducts']);
        Route::get('/products/featured', [ProductController::class, 'featuredProducts']);
        Route::get('/products/{slug}', [ProductController::class, 'show']);
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/categories/{slug}', [CategoryController::class, 'show']);
        
        Route::prefix("upload")->group(function() {
            Route::post('/file', [UploadsController::class, 'uploadFile']);
        });

        Route::prefix("orders")->group(function() {
            Route::post('/', [OrderController::class, 'store']);
            Route::get('/', [OrderController::class, 'index']);
        });

        // Route::apiResource('clinics', ClinicController::class);
        Route::apiResource('faqs', FAQController::class);

        Route::middleware(['verified', 'jwt.verify', 'auth:api'])->group(function () {
            Route::prefix("user")->group(function() {
                Route::get('/profile', [UserController::class, 'profile']);
                Route::post('/profile', [UserController::class, 'updateProfile']);
                Route::post('/deactivate', [UserController::class, 'deactivateAccount']);
                Route::post('/currency', [UserController::class, 'updateCurrency']);
            });
            // Route::apiResource('booking', ClinicTreatmentController::class);
        });
        
    });
});

Route::prefix('documentation')->group(function () {
    Route::get('/', function(){
        return redirect(env("POSTMAN_URL"));
    });
});
