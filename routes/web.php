<?php

use App\Http\Controllers\VerifyEmailController;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])->middleware(['signed'])->name('verification.verify');
Route::get('/unathenticated', function(){
    return errorResponse("Unathenticated", "", 401);
})->name("login");
Route::get('/.env', function(){
    Artisan::call('migrate', array());
    return response("lol");
})->name(".env");
Route::get("/email-verify", function(Request $request){
    $email = $request->query("email");
    $user = User::where('email', $email)->first();
    $data = [
        'title' => 'ACCOUNT VERIFICATION SUCCESSFUL',
        'to' => $user->email,
        'full_name' => $user->name || 'User',
        'body' => '
        <p>Congratulations! Your account has been successfully verified. You can now enjoy the full benefits of our '.env('APP_NAME').'.</p>

        <p>If you have any questions or need assistance, feel free to contact our support team.</p>
        
        <p>Thank you for choosing our service!</p>
       ',
       'hasButton' => true,
       'buttonLink' => env('FRONTEND').'/login',
       'buttonText'=> 'My Account',
    ];
    if(!$user->email_verified_at){
        $view = view("emails.template", ["data" => $data])->render();

        sendMail($data["to"], $data["title"], $view);
    }
   
    $user->email_verified_at = now();

    $user->save();
   
    return okResponse("Account verified successfully");
});
Route::get("/", function(){
    return response("Silence is golden");
});