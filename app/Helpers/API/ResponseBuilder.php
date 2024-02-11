<?php

namespace App\Helpers\API;

class ResponseBuilder
{
    // define code status
    const Success = 200;
    const Created = 201;
    const Forbidden = 403;
    const Not_Found = 404;
    const Bad_Request = 400;
    const Unauthorized = 401;
    const Server_Error = 500;
    const Validation_Error = 422;
    const Conflict = 409;


    public static function response($data = null, $message = null, $error = null, $status = null)
    {
        if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) {

            $custom = collect(['message' => $message]);

            $data = $custom->merge($data);

            return response()->json($data);
        } else {
            if ($data != null) {

                $response["data"] = $data;
            }


            $response["message"] = $message;


            if ($error != null) {
                $response["errors"] = $error;
            }

            return response()->json($response, $status);
        }
    }
}
