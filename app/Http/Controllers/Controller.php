<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;


#[OA\Info(
    version: '1.0.1',
    title: 'API for Event management system',
)]
#[OA\Server(
    url: '/api',
    description: 'Main endpoint'
)]
#[OA\SecurityScheme(
    securityScheme: 'apiKeyBearer',
    type: 'http',
    description: 'Bearer token authorization',
    name: 'Authorization',
    in: 'header',
    bearerFormat: 'string',
    scheme: 'bearer',
)]
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
