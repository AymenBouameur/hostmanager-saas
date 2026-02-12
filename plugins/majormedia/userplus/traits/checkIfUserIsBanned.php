<?php

namespace MajorMedia\UserPlus\Traits;

use MajorMedia\ToolBox\Exceptions\RESTErrorException;
use RainLab\User\Models\User;
use JWTAuth;
use Auth;

trait checkIfUserIsBanned
{

    public function checkIfUserIsBanned(User $user)
    {
        if ($user->isBanned()) {
            JWTAuth::setToken($token = JWTAuth::fromUser($user))->invalidate();
            Auth::logout();
            throw new RESTErrorException("Sorry, this user is currently not activated. Please contact us for further assistance.", 12450, 403);
        }
    }
}
