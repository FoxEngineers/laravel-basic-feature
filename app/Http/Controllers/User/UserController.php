<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected UserService $userService) {}

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = $this->userService->registerUser($request->validated());
            DB::commit();

            return $this->apiResponse(
                true,
                __('Thanks for registering! Please verify your email address to activate your account.'),
                ['user' => new UserResource($user)],
                Response::HTTP_CREATED
            );
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e; // Re-throw validation exceptions to maintain the same error format
        } catch (Exception $e) {
            DB::rollBack();
            // Log the exception for debugging purposes
            Log::error('User registration failed: '.$e->getMessage(), [
                'request' => $request->all(),
                'TraceAsString' => $e->getTraceAsString(),
            ]);

            return $this->apiResponse(
                false,
                __('Registration failed. Please try again later.'),
                [],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function me(Request $request)
    {
        return $this->apiResponse(
            true,
            'User profile fetched successfully.',
            ['user' => new UserResource($request->user())],
            Response::HTTP_OK
        );
    }
}
