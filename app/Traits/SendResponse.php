<?php 

namespace App\Traits;

trait SendResponse
{
    public function response_validate($errors)
    {
        $error_message = [];

        foreach ($errors->all() as $message) {
            $error_message[] = $message;
        }

        $response =[
            'status' => 'error',
            'message' => $error_message,
            'data' => null
        ];
        return $response;
    }
    
    public function response_success(string $message, $data)
    {
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ];

        return $response;
    }

    public function response_error(string $message, $data = null)
    {
        $response = [
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ];

        return $response;
    }
}



?>