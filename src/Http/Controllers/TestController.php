<?php

namespace STS\Serverless\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class TestController extends BaseController
{
    public function info(Request $request)
    {
        $results = [
            'php_ini' => php_ini_loaded_file(),
            'parameters' => $request->all(),
            'headers' => $request->headers->all()
        ];
        $headers = [
            'x-uri' => $request->getUri()
        ];

        return \Illuminate\Http\JsonResponse::create($results, 200, $headers);
    }
}
