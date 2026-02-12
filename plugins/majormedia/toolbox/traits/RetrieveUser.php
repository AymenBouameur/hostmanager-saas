<?php namespace Majormedia\ToolBox\Traits;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use JWTAuth;

trait RetrieveUser{
    use \Majormedia\ToolBox\Traits\GetCurrentUserFromJWT;
    use JsonAbort;

    public function retrieveUser($silent=false){
        $err = false;

        try{
            $token = $this->getToken($silent);

            if(!$this->user = JWTAuth::toUser($token)){
                $err = true;
                $code = \ErrorCodes::TOKEN_INCORRECT;
            }
        } catch(TokenExpiredException $ex){
            $code = \ErrorCodes::TOKEN_EXPIRED;
            $err = true;
        } catch(TokenInvalidException $ex){
            $code = \ErrorCodes::TOKEN_INVALID;
            $err = true;
        } catch(\Exception $ex){
            $code = \ErrorCodes::AUTHENTICATION_ERROR;
            $err = true;
        }

        // if error occurs return it and exit
        // if silent is true don't abort
        if($err && !$silent){
        	$this->JsonAbort([
                'status' => 'error',
                'code' => $code,
            ], 400);
        }

        return $this->user;
    }

    public function getToken($silent=false){
        $req = request();

        // check authorization and token headers for token
        $token = explode(" ", 
            $req->header("authorization") ?? $req->header("token")
        )[1] ?? null;

        if(!$token)
            $token = \Request::input('token', null);

        // if silent is true don't abort
        if(!$token && !$silent)
            return $this->JsonAbort([
                'status' => 'error',
                'code' => \ErrorCodes::BEARER_TOKEN_NOT_PROVIDED,
            ], 400);

        return $token;
    }

    public function requestAuthenticated(){
        return $this->getToken(true) != null;
    }
}