<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:api');
    // }
    

    /**
     * @OA\Get(
     *      path="/api/v1/user/profile",
     *      operationId="userProfile",
     *      tags={"Users"},
     *      summary="Get user profile",
     *      description="Returns user profile",
     *      security={{ "apiAuth": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent()
     *       ),
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
     *     )
     *
     * Returns user's object
     */
   
    public function profile(){
        $user = auth()->user();
        $user->notifications = $user->notifications ? json_decode($user->notifications, true) : [];
        return okResponse("Fetched user successfully", $user);
    }


    /**
     * @OA\Post(
     *      path="/api/v1/user/profile",
     *      operationId="UpdateUserProfile",
     *      tags={"Users"},
     *      summary="Update profile",
     *      description="Update profile",
     *      security={{ "apiAuth": {} }},
     *     @OA\RequestBody(
    *         description="Update user's profile",
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *                 type="object",
    *                 @OA\Property(
    *                     property="file",
    *                     description="image file to upload",
    *                     type="string",
    *                     format="binary",
    *                 ),
    *                 @OA\Property(
    *                     property="first_name",
    *                     description="User's first name",
    *                     type="string",
    *                     format="",
    *                 ),
    *                 @OA\Property(
    *                     property="last_name",
    *                     description="User's Lastname",
    *                     type="string",
    *                 ),
    *              @OA\Property(
    *                     property="phone_number",
    *                     description="User's Phone number",
    *                     type="string",
    *                 ),
    *                  @OA\Property(
    *                     property="email",
    *                     description="User's email address",
    *                     type="string",
    *                     format="email",
    *                 ),
    *                  @OA\Property(
    *                     property="dob",
    *                     description="User's DOB (YYYY-MM-DD)",
    *                     type="string",
    *                      format="date"
    *                 ),
    *             ),
    *         ),
    *     ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent()
     *       ),
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
     *     )
     *
     * Returns list of users
     */
    public function updateProfile(Request $request){
        $validator = Validator::make( $request->all(),  [
            "first_name" => "string|min:3|max:30",
            "last_name" => "string|min:3|max:30",
            "phone_number" => "string",
            "country_code" => "string",
            "dob" => "string",
            "email" => "email",
        ]);
        if($validator->fails()) {
            $erro = json_decode($validator->errors(), true);
            $msg = array_values($erro)[0];
            return errorResponse( $msg[0], $erro);
        }

        // set variables for user profile
        $user = auth()->user();
        $first_name = $request->first_name ? $request->first_name : $user->first_name; 
        $last_name = $request->last_name ? $request->last_name : $user->last_name; 
        $user->name = $first_name.' '.$last_name;
        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->phone_no = $request->phone_number ? $request->phone_number : $user->phone_no; 
        $user->country_code = $request->country_code ? $request->country_code : $user->country_code; 
        $user->dob = $request->dob ? $request->dob : $user->dob; 
        $user->language = $request->language ? $request->language : $user->language; 

        if($request->file("file")){
            $image = $request->file('file');
            $extension = $image->extension();
            if(!in_array($extension, ["png", "jpg", "jpeg"])){
                return errorResponse("Invalid image uploaded");
            }

            $uploadImage = uploadFile($request);
            if(!$uploadImage){
                return errorResponse("We hit a snag, unable to upload image.");
            }
            $user->profile_image = $uploadImage["url"];
        }
        if($request->dob){
            $pastDate = Carbon::parse($request->dob);

            // Today's date
            $todayDate = Carbon::now();

            if ($todayDate->gt($pastDate)) {
            }else{
                return errorResponse( "Date of Birth should be before today's date.");
            }
        }
        $user->save();

        if($request->email){
            if($request->email == $user->email) {
                return okResponse("Profile updated", $user);
            }
            $token = randomToken();
            $user->email_verified_at = null;
            $user->verification_token = $token;
            $user->email = $request->email;
            $user->save();
    
            $data = [
                'title' => 'RE: ACCOUNT VERIFICATION',
                'to' => $request->email,
                'full_name' => $user->name,
                'body' => '
                <p>Thank you for registering! To complete your registration and verify your email address, below is the OTP to verify your account or click the button below:</p>
                <p class="otp">'.$token.'</p>
               ',
               'hasButton' => true,
               'buttonLink' => env('FRONTEND').'/email-verify?email='.$request->email.'&token='.$token,
               'buttonText'=> 'Verify Email',
               'hint' => '
               <p>If you\'re unable to click the button, you can also copy and paste the following link into your browser:</p>
        
               <p>'.env('FRONTEND').'/email-verify?email='.$request->email.'&token='.$token.'</p>'
            ];
            $view = view("emails.template", ["data" => $data])->render();
            try {
                sendMail($request->email, $data["title"], $view);
            } catch (\Throwable $th) {
                return errorResponse("Unable to send OTP, please check your email address", $user);
            }
        }
        return okResponse($request->email ? "Profile updated, check your email for OTP to verify your new email address." : "Profile updated", $user);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/user/deactivate",
     *      operationId="deactivateAccount",
     *      tags={"Users"},     
     *      security={{ "apiAuth": {} }},
     *      summary="Deactivate account",
     *      description="Deactivate user account",
     *     @OA\Parameter(
     *          name="reason",
     *          description="reason",
     *          in="query",
     *          required=false,
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
    public function deactivateAccount(Request $request){
        $validator = Validator::make( $request->all(),  [
            "reason" => "string",
        ]);
        if($validator->fails()) {
            $erro = json_decode($validator->errors(), true);
            $msg = array_values($erro)[0];
            return errorResponse( $msg[0], $erro);
        }
        $user = auth()->user();
        $user->reason = $request->reason;
        $user->save();
        $user->deleted_at = now();
        $user->delete();
        return okResponse("Thank you, account deactivated");
    }
    public function notifications(Request $request){
        $validator = Validator::make( $request->all(),  [
            "notifications" => "required",
        ]);
        
        if($validator->fails()) {
            $erro = json_decode($validator->errors(), true);
            $msg = array_values($erro)[0];
            return errorResponse( $msg[0], $erro);
        }
        $json = $request->notifications;
        $notifications = array_keys($json);
        foreach ($notifications as $notification) {
            if(!in_array($notification, User::$notifications)){
                return errorResponse( "Invalid data", null);
            }
        }
        $user = auth()->user();
        $user->notifications = json_encode($json);
        $user->save();
        return okResponse("Profile updated!");
    }
    public function updateCurrency(Request $request){
        $validator = Validator::make( $request->all(),  [
            "currency" => "string|required",
        ]);

        if($validator->fails()) {
            $erro = json_decode($validator->errors(), true);
            $msg = array_values($erro)[0];
            return errorResponse( $msg[0], $erro);
        }
        if(!in_array($request->currency, User::$currencies)){
            return errorResponse( "Invalid currency", null);
        }
        $user = auth()->user();
        $user->currency = strtoupper($request->currency);
        $user->save();
        return okResponse("Profile updated");
    }
    
    
}
