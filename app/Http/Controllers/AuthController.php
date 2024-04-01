<?php

namespace App\Http\Controllers;

use App\Events\ForgotPasswordEvent;
use App\Mail\PasswordResetMail;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use App\Models\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="Endpoints related to users"
 * )
 * @OA\Tag(
 *     name="Users/Auth",
 *     description="Endpoints related to user authentication"
 * )
 * * @OA\Tag(
 *     name="Users/Documents",
 *     description="Endpoints related to user documents"
 * )
 *  * @OA\Tag(
 *     name="Users/Feedback",
 *     description="Endpoints related to user feedback"
 * )
 */
    
class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'verifyAccount', 'register', 'resendVerificationEmail', 'forgotPassword', 'resetPassword', 'resetToken']]);
    }


    /**
     * @OA\Post(
     *      path="/api/v1/register",
     *      operationId="register",
     *      tags={"Users/Auth"},
     *      summary="Register user",
     *      description="Create new user",
     *     @OA\Parameter(
     *          name="email",
     *          description="Email Field",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="password",
     *          description="Password",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *      @OA\Response(
     *          response=201,
     *          description="Created",
    *          @OA\JsonContent()
    *       ),
    *      @OA\Response(
    *          response=200,
    *          description="Success operation",
    *          @OA\JsonContent()
    *       ),
    *      @OA\Response(response=400, description="Bad request"),
    *      @OA\Response(response=404, description="Resource Not Found"),
     *     )
     *
     * Create account for new user
     */
    public function register(Request $request)
    {
        $validated = $request->all();
        $validator = Validator::make($validated, [
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
            'newsletter' => 'integer|nullable|in:1'
        ]);

        if($validator->fails()) {
            $erro = json_decode($validator->errors(), true);
            $msg = array_values($erro)[0];

            return errorResponse( $msg[0], $erro);
        }
        $newsletter = (isset($validated['newsletter'])) ? $validated['newsletter'] : 0;

        $token = randomToken();
        $user = User::create([
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'newsletter' => $newsletter,
            'verification_token' => $token,
            'user_id' => $this->generateUUID(),
            'notifications' => json_encode(["promotions" => true,"reminders" => true,"updates" => true])
        ]);
        $data = [
            'title' => env("APP_NAME"). ' - EMAIL VERIFICATION',
            'to' => $user->email,
            'full_name' => $user->name || 'User',
            'body' => '
            <p>Thank you for registering! To complete your registration and verify your email address, below is the OTP to verify your account or click the button below:</p>
            <p class="otp">'.$token.'</p>
           ',
           'hasButton' => true,
           'buttonLink' => env('FRONTEND').'/email-verify?email='.$user->email.'&token='.$token,
           'buttonText'=> 'Verify Email',
           'hint' => '
           <p>If you\'re unable to click the button, you can also copy and paste the following link into your browser:</p>
    
           <p>'.env('FRONTEND').'/email-verify?email='.$user->email.'&token='.$token.'</p>'
        ];
        $view = view("emails.template", ["data" => $data])->render();

        $sendMail = sendMail($data["to"], $data["title"], $view);
        if($sendMail == false){
            User::where("email", $user->email)->delete();
            return errorResponse( 'Something went wrong', [], 400);

        }
        // try {
        //     event(new Registered($user));
        // } catch (\Exception $e) {
           
        //     return errorResponse("Failed to create user",  $e->getMessage(), 500);
        // }

        return okResponse("user created");
    }



     /**
    * @OA\Post(
    *     path="/api/v1/login",
    *     operationId="login",
    *     summary="Logs in user",
     *      tags={"Users/Auth"},
    *     description="Logs in user and gives access to authenticated resources",
    *     @OA\Parameter(
     *          name="email",
     *          description="Email Field",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="password",
     *          description="Password",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
    *     @OA\Response(response="200", description="Display a credential User."),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *       @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
    * )
    */
    public function login(Request $request)
    {
        $validated = $request->all();
        $validator = Validator::make($validated, [
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        if($validator->fails()) {
            $erro = json_decode($validator->errors(), true);
            $msg = array_values($erro)[0];
           

            return errorResponse( $msg[0], $erro);
        }
        
        if (!$token=auth()->attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            return errorResponse("Invalid login combination", 400);
        }
 
        try {
            $user = User::where('email', $validated['email'])->first();
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }

        if ($user->email_verified_at === null) {
            return errorResponse("Email not verified");

        }
        return $this->createNewToken($token);
    }

    public function createNewToken($token)
    {
        return response()->json([
            'code' => 200,
            'status'=> 'success',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL()*600,
            'user' => auth()->user()
        ]);
    }

    public function profile()
    {
        return response()->json(auth()->user());
    }



    /**
    * @OA\Post(
    *     path="/api/v1/logout",
    *     tags={"Users/Auth"},
    *     operationId="logout",
    *     summary="Logs out user",
    *     description="Logs out user",
    *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent()
     *       ),
    *      security={{ "apiAuth": {} }},
    * )
    */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Logged out']);
    }



    /**
     * @OA\Post(
     *      path="/api/v1/resend-verification",
    *     tags={"Users/Auth"},
     *      operationId="resendVerificationEmail",
     *      summary="Resend email verification email",
     *      description="Resend email verification email",
     *     @OA\Parameter(
     *          name="email",
     *          description="Email",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent()
     *       ),
     *     )
     *
     */
    public function resendVerificationEmail(Request $request)
    {
        $validated = $request->validate(['email' => 'required|email|string']);
        try {
            $user = User::where('email', $validated['email'])->first();
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], 400);

        }

        if (!isset($user)) {
            return errorResponse('Invalid email address');
        }

        if ($user->email_verified_at !== null) {
            return errorResponse('Email already verified');
        }

        $token = randomToken();
        $user->verification_token = $token;
        $user->save();

        $data = [
            'title' => 'EMAIL VERIFICATION',
            'to' => $user->email,
            'full_name' => $user->name || 'User',
            'body' => '
            <p>Thank you for registering! To complete your registration and verify your email address, below is the OTP to verify your account or click the button below:</p>
            <p class="otp">'.$token.'</p>
           ',
           'hasButton' => true,
           'buttonLink' => env('FRONTEND').'/email-verify?email='.$user->email.'&token='.$token,
           'buttonText'=> 'Verify Email',
           'hint' => '
           <p>If you\'re unable to click the button, you can also copy and paste the following link into your browser:</p>
    
           <p>'.env('FRONTEND').'/email-verify?email='.$user->email.'&token='.$token.'</p>'
        ];
        $view = view("emails.template", ["data" => $data])->render();

        sendMail($data["to"], $data["title"], $view);
        return okResponse("Verification mail sent");
    }



    /**
     * @OA\Post(
     *      path="/api/v1/verify-account",
    *     tags={"Users/Auth"},
     *      operationId="verifyAccount",
     *      summary="Resend email verification email",
     *      description="Resend email verification email",
     *     @OA\Parameter(
     *          name="email",
     *          description="Email",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *  @OA\Parameter(
     *          name="token",
     *          description="Token",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent()
     *       ),
     *     )
     *
     */
    public function verifyAccount(Request $request)
    {
        $validated = $request->validate(['email' => 'required|email|string', "token" => 'required|string']);
        try {
            $user = User::where('email', $validated['email'])->first();
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], 400);

        }

        if (!isset($user)) {
            return errorResponse('Invalid email address');
        }

        if ($user->email_verified_at !== null) {
            return errorResponse('Email already verified');
        }
        return response($user->verification_token);
        if ($user->verification_token !== trim($validated['token'])) {
            return errorResponse('Invalid OTP');
        }


        $token = randomToken();
        $user->email_verified_at = now();
        $user->save();

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
        $view = view("emails.template", ["data" => $data])->render();

        sendMail($data["to"], $data["title"], $view);
        return okResponse("Account verified successfully");
    }



    /**
     * @OA\Post(
     *      path="/api/v1/change-password",
     *      operationId="changePassword",
     *      tags={"Users/Auth"},
     *      summary="Change user password",
     *      description="Change user password",
     *      security={{ "apiAuth": {} }},
     *     @OA\Parameter(
     *          name="password",
     *          description="Password",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *      @OA\Parameter(
     *          name="password_confirmation",
     *          description="Password",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent()
     *       ),
     *     )
     *
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate(['current_password' => "required", 'password' => 'required|string|confirmed|min:8']);
        try {
            $user= User::where('email', auth()->user()->email)->first();
            if(!$user){
                return errorResponse("User not found");
            }
            if(!Hash::check($validated["current_password"], $user->password))return errorResponse("Current password is invalid");
            User::where('email', auth()->user()->email)
                ->update(['password' => bcrypt($validated['password'])]);
                return okResponse("Password updated");
        } catch (\Exception $e) {
            return response()->json(['message' => 'Password update failed']);
        }
    }



    /**
     * @OA\Post(
     *      path="/api/v1/forgot-password",
     *      operationId="forgotPassword",
     *      tags={"Users/Auth"},
     *      summary="Initiate password reset process",
     *      description="Send password reset link to email",
     *     @OA\Parameter(
     *          name="email",
     *          description="email",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent()
     *       ),
     *     )
     *
     */
    public function forgotPassword(Request $request)
    {
        $validated = $request->validate(['email' => 'required|string|email']);
        try {
            $user = User::where('email', $validated['email'])->first();    
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], 400);
        }

        if (!isset($user)) {
            return errorResponse("User not found", [], 400);

        }

        $token = randomToken();
        try {
            // event(new ForgotPasswordEvent($request->email));
            // ForgotPasswordEvent::dispatch($request->email);
            $data = [
                'title' => 'PASSWORD RESET Request',
                'to' => $user->email,
                'full_name' => $user->name || 'User',
                'body' => '
                <p>We received a request to reset your password. To complete the process, use the following One-Time Password (OTP):</p>
                <p class="otp">'.$token.'</p>
                <p>If you didn\'t request a password reset, please ignore this email. The OTP is valid for a short period.</p>
               ',
            ];
            $view = view("emails.template", ["data" => $data])->render();

            if(!sendMail($data["to"], $data["title"], $view)){
                return errorResponse("Something went wrong, unable to send OTP", [], 400);
            }
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], 400);
        }

        try {
            PasswordReset::updateOrCreate(
                ['email' => $validated['email']],
                ['token' => $token],
                ['created_at' => Carbon::now()->format('Y-m-d H:i:s')]
            );
            return okResponse('Check your mail for the OTP', []);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], 400);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/reset-password/{token}",
     *      operationId="validateResetToken",
     *      tags={"Users/Auth"},
     *      summary="confirm password reset token",
     *      description="Confirm the password reset token",
     *     @OA\Parameter(
     *          name="token",
     *          description="token",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="successful validated",
     *          @OA\JsonContent()
     *       ),
     *     )
     *
     */
    public function resetToken($token)
    {
        $token = PasswordReset::where('token', $token)->first();
        if(!$token){
            return errorResponse("Invalid OTP");
        }
        $user = User::where("email", $token->email)->first();
        if(!$user){
            return errorResponse("Invalid OTP");
        }
        $user->token = $token->token;
        return okResponse("Token is valid", $user);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/reset-password",
     *      operationId="resetPassword",
     *      tags={"Users/Auth"},
     *      summary="Reset password",
     *      description="Reset user password",
     *     @OA\Parameter(
     *          name="email",
     *          description="email",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *      @OA\Parameter(
     *          name="token",
     *          description="token",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *      @OA\Parameter(
     *          name="password",
     *          description="password",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *      @OA\Parameter(
     *          name="password_confirmation",
     *          description="password",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent()
     *       ),
     *     )
     *
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|string',
            'token' => 'required',
            'password' => 'required|string|confirmed|min:8'
        ]);

        try {
            $user = PasswordReset::where('email', $validated['email'])
                                ->where('token', $validated['token'])
                                ->first();  
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], 400);
        }

        if (!isset($user)) {
            return errorResponse("Wrong credentials", [], 422);
        }

        try {
            PasswordReset::where('token', $validated['token'])->delete();
            User::where('email', $validated['email'])
                ->update(['password' => bcrypt($validated['password'])]);
            return okResponse("Password reset successfully");
        } catch (\Exception $e) {
            return errorResponse("Password reset failed");
        }
    }

    protected function generateUUID(){
        $u = \Illuminate\Support\Str::uuid();
        if(User::where("user_id", $u)->first()) return $this->generateUUID();
        return $u;
    }
}
