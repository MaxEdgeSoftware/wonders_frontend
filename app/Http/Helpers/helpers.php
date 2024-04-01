<?php
// use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!function_exists("randomToken")) {
    function randomToken($length = 6, $type = 'number')
    {
        $random = "123456789";
        if($type == "text"){
            $random = "ABCDEFHIJKLMNOPQRSTUVYZYX1234567890";
        }
        return substr(str_shuffle($random), 0, $length);
    }
}


if (!function_exists("errorResponse")) {
    function errorResponse($msg = "", $errors = "", $code = 422)
    {
        return response()->json([
            'code' => $code,
            'status' => 'error',
            'errors' => $errors,
            'message' => $msg
        ], $code);
    }
}

if (!function_exists("simplePagination")) {
    function simplePagination($data, $count, $page, $limit)
    {
        return [
            'data' => $data,
            'limit' => $limit,
            'currentPage' => $page,
            'totalRecords' => $count,
            'totalPages' => ceil($count/$limit),
        ];
    }
}


if (!function_exists("okResponse")) {
    function okResponse($msg = "", $response = [])
    {
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'data' => $response,
            'message' => $msg
        ], 200);
    }
}


if (!function_exists("uploadFile")) {
    function uploadFile(Request $request, $key = "")
    {
        try {
            //code...
            $upload = Cloudinary::uploadFile($request->file($key != "" ? $key: "file")->getRealPath());

        return [
            "url" => $upload->getSecurePath(),
            "size"  =>$upload->getSize(),
            "size_in_kb"  => $upload->getReadableSize(),
            "file_type"  =>$upload->getFileType(),
            "file_name"=>$upload->getFileName(),
            "file_id"  =>$upload->getPublicId(),
            "ext"  =>$upload->getExtension(),
            "width"  =>$upload->getWidth(),
            "height"  =>$upload->getHeight(),
            "uploaded_at"  => $upload->getTimeUploaded(),
        ];
        // Upload an Image File to Cloudinary with One line of Code
        } catch (\Throwable $th) {
            return false;
        }
    }
}

if (!function_exists("uploadFiles")) {
    function uploadFiles(Request $request, $key = 'files')
    {
        $response = [];
        foreach ($request->file($key) as $k => $file) {
            $upload = Cloudinary::upload($file->getRealPath());
            $d = [
                "url" => $upload->getSecurePath(),
                "size"  =>$upload->getSize(),
                "size_in_kb"  => $upload->getReadableSize(),
                "file_type"  =>$upload->getFileType(),
                "file_name"=>$upload->getFileName(),
                "file_id"  =>$upload->getPublicId(),
                "ext"  =>$upload->getExtension(),
                "width"  =>$upload->getWidth(),
                "height"  =>$upload->getHeight(),
                "uploaded_at"  => $upload->getTimeUploaded(),
            ];
            array_push($response, $d);
        }
        return $response;
        // Upload an Image File to Cloudinary with One line of Code
    }
}


if (!function_exists("sendMail")) {
    function sendMail($email, $subject, $body){
        require base_path("vendor/autoload.php");
        $mail = new PHPMailer(true);     // Passing `true` enables exceptions
 
        try {
            // Email server settings
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = env("MAIL_HOST2");             //  smtp host
            $mail->SMTPAuth = true;
            $mail->Username = env("MAIL_USERNAME2");   //  sender username
            $mail->Password = env("MAIL_PASSWORD2");       // sender password
            $mail->SMTPSecure = env("MAIL_ENCRYPTION");                  // encryption - ssl/tls
            $mail->Port = env("MAIL_PORT2");                          // port - 587/465
 
            $mail->setFrom(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"));
            $mail->addAddress($email);
            // $mail->addCC($request->emailCc);
            // $mail->addBCC($request->emailBcc);
 
            // $mail->addReplyTo('sender@example.com', 'SenderReplyName');
 
            // if(isset($_FILES['emailAttachments'])) {
            //     for ($i=0; $i < count($_FILES['emailAttachments']['tmp_name']); $i++) {
            //         $mail->addAttachment($_FILES['emailAttachments']['tmp_name'][$i], $_FILES['emailAttachments']['name'][$i]);
            //     }
            // }
 
            $mail->isHTML(true);                // Set email content format to HTML
 
            $mail->Subject = $subject;
            $mail->Body    = $body;
 
            // $mail->AltBody = plain text version of email body;
 
            if( !$mail->send() ) {
                return false;
            }
            
            else {
                return true;
            }
 
        } catch (Exception $e) {
            //var_dump($e->getMessage());
             return false;
        }
    }
}


