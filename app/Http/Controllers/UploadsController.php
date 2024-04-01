<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadsController extends Controller
{
    //
    
    public function uploadFile(Request $request){
        return okResponse('Upload success', uploadFile($request));
    }
}
