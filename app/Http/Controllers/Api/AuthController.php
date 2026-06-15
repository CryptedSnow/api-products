<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Interfaces\AuthInterface;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(private AuthInterface $authInterface) {}

    #[OA\Post(
        path: '/register',
        summary: 'Registrar novo usuário',
        tags: ['AuthController'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name',                  type: 'string', example: 'User test'),
                    new OA\Property(property: 'email',                 type: 'string', example: 'user@email.com'),
                    new OA\Property(property: 'password',              type: 'string', example: 'password'),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Usuário criado'),
            new OA\Response(response: 422, description: 'Erro de validação'),
        ]
    )]
    public function registerUser(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = $this->authInterface->createUser($request->only(['name', 'email', 'password']));

        $token = $this->authInterface->createToken($user);

        return response()->json([
            'message'    => "Usuário $user->name criado com sucesso!",
            'user'       => new UserResource($user),
            'token'      => $token,
            'token_type' => 'Bearer'
        ], Response::HTTP_CREATED);
    }

    #[OA\Post(
        path: '/login',
        summary: 'Autenticar usuário',
        tags: ['AuthController'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email',    type: 'string', example: 'user@email.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login realizado'),
            new OA\Response(response: 422, description: 'Credenciais inválidas'),
        ]
    )]
    public function loginUser(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = $this->authInterface->findUserByEmail($request->email);

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        $this->authInterface->revokeAllTokens($user);

        $token = $this->authInterface->createToken($user);

        return response()->json([
            'message' => "$user->name realizou login!",
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    #[OA\Post(
        path: '/logout',
        security: [['bearerAuth' => []]],
        summary: 'Logout de usuário autenticado',
        tags: ['AuthController'],
        responses: [
            new OA\Response(response: 200, description: 'Logout realizado'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $userName = $user->name;

        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => "$userName fez logout!"
        ]);
    }

    #[OA\Get(
        path: '/profile',
        security: [['bearerAuth' => []]],
        summary: 'Obter perfil do usuário autenticado',
        tags: ['AuthController'],
        responses: [
            new OA\Response(response: 200, description: 'Perfil do usuário recuperado'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function profileUser(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user())
        ]);
    }

}
