<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'API Produto',
    version: '1.0.0',
    description: 'API REST para gerenciamento de produtos',
)]
#[OA\Server(
    url: 'http://localhost:8000/api',
    description: 'Servidor local (Docker)'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
)]
class SwaggerInfo {}
