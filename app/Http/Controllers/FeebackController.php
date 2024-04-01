<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeebackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }


     /**
     * @OA\Post(
     *      path="/api/v1/user/feedback",
     *      operationId="userFeedback",
     *      tags={"Users/Feedback"},
     *      security={{ "apiAuth": {} }},
     *      summary="User Feedback",
     *      description="Send a feedback",
     *     @OA\Parameter(
     *          name="about",
     *          description="About Field",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="category",
     *          description="Category",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="comments",
     *          description="Comments",
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
     * Create new user feedback
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),  [
            "about" => "required|string|min:2|max:100",
            "category" => "required|string|min:2",
            "comments" => "required|string",
        ]);

        if($validator->fails()) {
            $erro = json_decode($validator->errors(), true);
            $msg = array_values($erro)[0];
            return errorResponse( $msg[0], $erro);
        }
$id =  $this->generateUUID();
        $feedback = new Feedback;
        $feedback->id =$id;
        $feedback->about = $request->about;
        $feedback->user_id = auth()->user()->user_id;
        $feedback->category = $request->category;
        $feedback->comments = $request->comments;
        $feedback->save();
        $feedback->id = $id;
        $title = 'NEW FEEDBACK RECEIVED';
        if(auth()->user()->name){
            $title = 'NEW FEEDBACK RECEIVED FROM '. auth()->user()->name;
        }
        $data = [
            'title' => $title,
            'to' => env("NOTIFY_EMAIL"),
            'full_name' => 'Admin',
            'body' => '
            <p>New Feedback Received.</p>
            <p>About : '.$feedback->about.'</p>
            <p>Category : '.$feedback->category.'</p>
            <p>comment : <br /> '.$feedback->comments.'</p>
           ',
        ];
        $view = view("emails.template", ["data" => $data])->render();

        sendMail($data["to"], $data["title"], $view);
        return okResponse("Thank you for your feedback", $feedback);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    protected function generateUUID(){
        $u = \Illuminate\Support\Str::uuid();
        if(Feedback::where("id", $u)->first()) return $this->generateUUID();
        return $u;
    }
}
