<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\UpdateUserActiveStatusRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Laravel\Passport\Contracts\OAuthenticatable;

class UserController extends Controller
{
    /**
     * Update a user's active status
     *
     * @return JsonResponse
     */
    public function updateActiveStatus(UpdateUserActiveStatusRequest $request, int $id)
    {
        $user = User::find($id);
        if (! $user) {
            return $this->apiResponse(
                false,
                __('tle-validation.user.not_found'),
                null,
                404
            );
        }

        // Prevent admin from changing their own active status
        if ($user->id === auth()->id()) {
            return $this->apiResponse(
                false,
                __('You cannot deactivate your own account.'),
                null,
                403
            );
        }

        // Check if user is being deactivated
        if ($user->is_active && ! $request->is_active) {
            if ($user instanceof OAuthenticatable) {
                $user->tokens()->delete();
            }
        }

        $user->is_active = $request->is_active;
        $user->save();

        $status = $request->is_active ? 'activated' : 'deactivated';

        return $this->apiResponse(
            true,
            "User has been $status successfully",
            ['user' => $user]
        );
    }
}
