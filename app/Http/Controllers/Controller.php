<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

abstract class Controller
{
    /**
     * Returns a success response with the given message and data.
     *
     * @param  string|null  $message  the success message
     * @param  mixed  $data  optional additional data to return
     * @param  int  $status  the HTTP status code
     */
    protected function successResponse(?string $message = null, mixed $data = null, int $status = Response::HTTP_OK): JsonResponse
    {
        return $this->response(true, $message, $data, $status);
    }

    /**
     * Returns an error response with the given message and data.
     *
     * @param  string|null  $message  the error message
     * @param  mixed  $data  optional additional data to return
     * @param  int  $status  the HTTP status code
     */
    protected function errorResponse(?string $message = null, mixed $data = null, int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return $this->response(false, $message, $data, $status);
    }

    /**
     * Returns a response with the given success status, message, and data.
     *
     * @param  bool  $success  the success status
     * @param  string|null  $message  the message
     * @param  mixed  $data  optional additional data to return
     * @param  int  $status  the HTTP status code
     */
    private function response(bool $success, ?string $message = null, mixed $data = null, int $status = Response::HTTP_OK): JsonResponse
    {
        $res = ['success' => $success];

        if (! empty($message)) {
            $res['message'] = $message;
        }

        if ($data !== null) {
            $res['data'] = $data;
        }

        return response()->json($res, $status);
    }
}
