<?php

namespace App\Http\Controllers\Api;

use App\Dto\ApiErrorResponseDto;
use App\Dto\AuthSuccessDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Virtual\HttpForbiddenResponse;
use App\Virtual\HttpUnauthorizedResponse;
use App\Virtual\HttpValidationErrorResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/take-token',
        operationId: 'authLogin',
        description: 'Get API token',
        summary: 'Return API token if credentials are correct.',
        tags: ['Authentication']
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(ref: LoginRequest::class),
    )]
    #[OA\Response(
        response: 200,
        description: 'Success auth',
        content: [new OA\JsonContent(ref: AuthSuccessDto::class)]
    )]
    #[HttpValidationErrorResponse]
    #[HttpForbiddenResponse]
    public function takeToken(LoginRequest $request): JsonResponse
    {
        if (Auth::attempt($request->validated())) {
            $authDto = new AuthSuccessDto(Auth::user()->createToken('api-token')->plainTextToken);

            return response()->json((array)$authDto);

        }

        return response()->json(
            (array)new ApiErrorResponseDto('The provided credentials are incorrect.'),
            403
        );
    }

    #[OA\Delete(
        path: '/invalidate-token',
        operationId: 'invalidatedAccessToken',
        description: 'Invalidate access token',
        security: [['apiKeyBearer' => []]],
        tags: ['Authentication'],
    )]
    #[OA\Response(
        response: 204,
        description: 'Token was invalidated',
        content: [new OA\JsonContent()]
    )]
    #[HttpUnauthorizedResponse]
    public function invalidateToken(Request $request): Response
    {
        $request->user()->tokens()->delete();

        return response()->noContent();
    }
}
