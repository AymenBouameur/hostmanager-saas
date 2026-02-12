<?php namespace Majormedia\ToolBox\Traits;
use Exception;
use Majormedia\UserPlus\Models\Role;

trait RestrictToRoles{
    use JsonAbort;
    use RetrieveUser;

    public function restrictToRoles(Array $roles){
        // get the user
        $this->retrieveUser();

        if(!in_array($this->user->role_id, $roles))
            $this->JsonAbort([
                'status' => 'error',
                'code' => \ErrorCodes::CANT_ACCESS_RESOURCE,
            ], 400);

        return $this->user;
    }
}

        