<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function makeResponse($success, $message, $data = NULL) {
        $response = [
            'success' => $success,
            'message' => $message,
        ];


        if($data != NULL){
            $response['data'] = $data;
        }

        return $response;
    }

    // Mengirim response berhasil
    public function sendResponse($message, $result = NULL)
    {
    	$response = $this->makeResponse(true, $message, $result);

        return response()->json($response, 200);
    }


    // Mengirim response gagal
    public function sendError($error, $errorMessages = NULL, $code = 500)
    {
    	$response = $this->makeResponse(false, $error, $errorMessages);

        return response()->json($response, $code);
    }
}
