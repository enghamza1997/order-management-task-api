<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use \Symfony\Component\HttpFoundation\Cookie;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    /**
     * @param mixed $data
     * @param string $msg
     * @param int $code
     * @param bool $success
     */
    public function success_response(mixed $data, string $msg = null, int $code = 200, bool $success = true, array $extra = [])
    {
        return response()->json([
            'success' => $success,
            'message' => $msg,
            'data' => $data
        ] + $extra, $code);
    }
    /**
     * @param mixed $data
     * @param \Symfony\Component\HttpFoundation\Cookie $cookie
     * @param string $msg
     * @param int $code
     * @param bool $success
     */
    public function response_wc(mixed $data, Cookie $cookie, string $msg, int $code = 200, bool $success = true, array $extra = [])
    {
        return response()->json([
            'success' => $success,
            'message' => $msg,
            'data' => $data
        ] + $extra, $code)->withCookie($cookie);
    }
    /**
     * @param string $msg
     * @param int $code
     * @param bool $success
     */
    public function error_response(string $msg, int $code, bool $success = false)
    {
        return response()->json([
            'success' => $success,
            'message' => $msg
        ], $code);
    }
}
