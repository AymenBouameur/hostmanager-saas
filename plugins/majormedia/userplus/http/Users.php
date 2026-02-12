<?php

namespace MajorMedia\UserPlus\Http;

use Auth;
use Event;
use JWTAuth;
use Request;
use Response;
use Exception;
use Carbon\Carbon;
use System\Models\File;
use RainLab\User\Models\User;
use Backend\Classes\Controller;
use MajorMedia\UserPlus\Models\Role;
use MajorMedia\ToolBox\Utility\ErrorCodes;
use MajorMedia\ToolBox\Traits\RetrieveUser;
use MajorMedia\ToolBox\Traits\GetValidatedInput;
use RainLab\User\Models\Settings as UserSettings;
use Intervention\Image\ImageManagerStatic as Image;
use MajorMedia\ToolBox\Traits\JsonAbort;
/**
 * Users Back-end Controller
 */

class Users extends Controller
{
    use GetValidatedInput;
    use RetrieveUser;
    use JsonAbort;


    public $implement = [
        'MajorMedia.ToolBox.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';


    /**
     * Check registration ability
     *
     * @return boolean
     */
    public function canRegister()
    {
        return UserSettings::get('allow_registration', true);
    }

    /**
     * Register
     *
     * @return void
     */
    public function register()
    {
        if (!$this->canRegister()) {
            $this->JsonAbort(
                [
                    'status' => 'error',
                    'code' => ErrorCodes::REGISTRATION_DISABLED,
                ],
                400
            );
        }
        /*
         * Validate input
         */
        $data = $this->getValidatedInput(
            ['email', 'password', 'password_confirmation', 'role_id', 'name', 'surname'],
            [
                'email' => 'required|between:6,255|unique:users',
                'password' => 'required|between:8,32',
                'password_confirmation' => 'required|between:8,32',
                'name' => 'nullable',
                'surname' => 'nullable'
            ]
        );

        if (!array_key_exists('username', $data)) {
            $data['username'] = input('email');
        }

        $this->ValidateOrFail(
            $data,
            (new User)->rules
        );

        /*
         * Register user
         */
        try {
            Event::fire('rainlab.user.beforeRegister', [&$data]);

            $automaticActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_AUTO;
            $user = Auth::register($data, $automaticActivation);

            // Associate the selected role
            Event::fire('rainlab.user.register', [$user, $data]);
        } catch (Exception $ex) {
            return Response::json(['status' => 'error', 'code' => ErrorCodes::UNEXPECTED], 400);
        }
        $now = Carbon::now();
        $user->refresh();
        $token = JWTAuth::fromUser($user);
        $payload = JWTAuth::getPayload($token);
        $expirationTimestamp = $payload->get('exp');
        $expirationDate = Carbon::createFromTimestamp($expirationTimestamp);

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'expires_in' => $expirationDate->toDateTimeString(),
            'user' => $user
        ]);
    }

    /**
     * Login
     *
     * @return void
     */
    public function login()
    {

        // validate input
        $data = $this->getValidatedInput(
            ['email', 'password',],
            [
                'email' => 'required|email',
                'password' => 'required',
            ]
        );


        // verify the credentials and create a token for the user
        if (!$token = JWTAuth::attempt($data))
            return $this->JsonAbort([
                'status' => 'error',
                'code' => ErrorCodes::INVALID_CREDENTIALS,
            ], 400);

        $user = JWTAuth::authenticate($token);
        // we do a check if user is banned
        Event::fire('majormedia.userplus::user.logged', [$user]);

        $now = Carbon::now();
        $user->last_seen = $now;
        $user->save();
        $user->refresh();
        $payload = JWTAuth::getPayload($token);
        $expirationTimestamp = $payload->get('exp');
        $expirationDate = Carbon::createFromTimestamp($expirationTimestamp);

        return $this->JsonAbort([
            'status' => 'success',
            'token' => $token,
            'expires_in' => $expirationDate->toDateTimeString(),
            'user' => $user
        ], 200);
    }

    function findAccount()
    {
        extract(
            $this->getValidatedInput(
                ['login'],
                [
                    'login' => 'required|exists:users,email',
                ]
            )
        );

        // retrive the user
        $user = User::where('email', $login)->first();

        // generate opt code and affect it to the user
        $code = random_int(100000, 999999);
        $user->otp_code = $code;
        $user->save();

        // Mail::send('chimatch.userplus::mail.otp_verification', ['user' => $user,'code' => $code], function ($message) use($user){
        //   $message->to($user->email, $user->full_name);
        //   $message->subject('chimatch.userplus::lang.mail.opt_verification.subject');
        // });

        return $this->JsonAbort([
            'status' => 'success',
            'otp' => $code
        ], 200);
    }

    function verifyOTP()
    {
        extract(
            $this->getValidatedInput(
                ['login', 'otp'],
                [
                    'login' => 'required|exists:users,email',
                    'otp' => 'required|int|min:100000',
                ]
            )
        );

        $user = User::where('email', $login)->first();

        // check if OTP is expired
        if (Carbon::now()->greaterThan(Carbon::parse($user->updated_at)->addHours(1)))
            $this->JsonAbort([
                'status' => 'error',
                'code' => \ErrorCodes::OTP_EXPIRED,
            ], 400);

        // if OTP incorrect abort
        if ($user->otp_code != $otp)
            return $this->JsonAbort([
                'status' => 'error',
                'code' => \ErrorCodes::OTP_INCORRECT,
            ], 400);

        $token = JWTAuth::fromUser($user);

        // range 10000 -> 99999 for password reset
        $user->otp_code = random_int(10000, 99999);
        $user->save();
        $user->refresh();

        return $this->JsonAbort([
            'status' => 'success',
            'next_otp' => $user->otp_code,
            'token' => $token,
        ], 200);
    }

    public function verifyPhoneNumber()
    {
        $this->retrieveUser();

        extract(
            $this->getValidatedInput(
                ['phone',],
                [
                    'phone' => 'required|regex:/^\+?\d{10,15}$/|exists:users,phone',
                ]
            )
        );
        // retrive the user
        $user = User::where('phone', $phone)->first();

        // generate opt code and affect it to the user
        $code = random_int(100000, 999999);
        $user->otp_code = $code;
        $user->save();
    }

    public function resetPassword()
    {
        $this->retrieveUser();

        // check is OTP is expired
        if (Carbon::now()->greaterThan(Carbon::parse($this->user->updated_at)->addHours(1)))
            $this->JsonAbort([
                'status' => 'error',
                'code' => \ErrorCodes::OTP_EXPIRED,
            ], 400);

        extract(
            $this->getValidatedInput(
                ['password', 'otp', 'password_confirmation'],
                [
                    'password' => $this->user->rules['password'],
                    'password_confirmation' => $this->user->rules['password_confirmation'],
                    'otp' => 'required|int|max:99999',
                ],
            )
        );

        if ($this->user->otp_code != $otp)
            return $this->JsonAbort([
                'status' => 'error',
                'error' => \ErrorCodes::OTP_INCORRECT,
            ], 400);

        $this->user->password = $password;
        $this->user->password_confirmation = $password_confirmation;
        $this->user->otp_code = null;
        $this->user->save();

        return $this->JsonAbort([
            'status' => 'success'
        ], 200);
    }

    public function updateProfile()
    {
        $this->retrieveUser();

        // require password ?
        $data = $this->getValidatedInput(
            [
                'name',
                'surname',
                'phone',
                'email',
                'avatar',
            ],
            [
                'name' => 'string',
                'surname' => 'string',
                'phone' => 'nullable|regex:/^\+?\d{10,15}$/',
                'email' => 'nullable|unique:users',
                'avatar' => 'nullable|string',
            ]
        );

        $this->user->fill($data);

        $this->user->save();

        if (isset($data['avatar'])) {
            $this->updateAvatar($data['avatar']);
            unset($data['avatar']);
        }

        $this->user->refresh();

        return $this->JsonAbort([
            'status' => 'success',
            'user' => $this->user,
        ], 200);
    }

    public function changePassword()
    {
        $this->retrieveUser();

        extract(
            $this->getValidatedInput(
                ['current_password', 'new_password', 'new_password_confirmation'],
                [
                    'current_password' => 'required|between:8,255',
                    'new_password' => 'required|between:8,255',
                    'new_password_confirmation' => 'required|between:8,255',
                ],
            )
        );

        if (!$this->user->checkPassword($current_password))
            return $this->JsonAbort([
                'status' => 'error',
                'code' => \ErrorCodes::PASSWORD_INCORRECT,
            ], 400);

        $this->user->password = $new_password;
        $this->user->password_confirmation = $new_password_confirmation;
        $this->user->save();

        return $this->JsonAbort(['status' => 'success'], 200);
    }

    public function updateAvatar($data)
    {
        $this->retrieveUser();

        $rawData = base64_decode($data);

        if (base64_encode($rawData) !== $data)
            return $this->JsonAbort([
                'status' => 'error',
                'code' => \ErrorCodes::AVATAR_INVALID,
            ], 400);

        // if file is not image abort
        if (!@getimagesizefromstring($rawData))
            return $this->JsonAbort([
                'status' => 'error',
                'code' => \ErrorCodes::AVATAR_INVALID,
            ], 400);

        $file = (new File)->fromData($rawData, str_random(40));
        $size = $file->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unit = preg_replace('/[0-9\.]/', '', $size);
        $index = array_search($unit, $units);
        $bytes = floatval($size) * pow(1024, $index);
        if ($bytes > (1 * 1024 * 1024)) {
            $img = Image::make($file);
            $img->resize(null, 500, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save($file->getPathname(), 60);
        }
        $this->user->avatar = $file;
        $this->user->save();
    }

    public function getProfile()
    {
        return $this->JsonAbort([
            'status' => 'success',
            'user' => $this->retrieveUser(),
        ], 200);
    }
    public function refreshToken()
    {
        $token = $this->getToken();
        $now = Carbon::now();
        $ttlMinutes = env('JWT_TTL', 60);
        $expirationDate = $now->clone()->addMinutes($ttlMinutes)->toDateTimeString();
        $user = null;

        try {
            $newToken = JWTAuth::refresh($token);
            $user = JWTAuth::toUser($newToken);
        } catch (\Tymon\JWTAuth\Exceptions\TokenBlacklistedException $e) {
            return $this->JsonAbort([
                'status' => 'error',
                'code' => \ErrorCodes::TOKEN_BLACKLISTED,
            ], 401);
        } catch (Exception $e) {
            // Other exceptions
            return $this->JsonAbort([
                'status' => 'error',
                'code' => \ErrorCodes::TOKEN_REFRESH_FAILED,
                'message' => $e->getMessage(),
            ], 401);
        }

        // Return the new token and user details
        return $this->JsonAbort([
            'status' => 'success',
            'token' => $newToken,
            'expires_in' => $expirationDate,
            'user' => $user,
        ], 200);
    }



    public function logout()
    {
        try {
            \JWTAuth::invalidate($this->getToken());
        } catch (\Exception $ex) {
        }
        return $this->JsonAbort([
            'status' => 'success',
        ], 200);
    }

    public function authenticateByToken()
    {
        $data = $this->getValidatedInput(
            ['token', 'email', 'image', 'device_token'],
            [
                'token' => 'required',
            ]
        );

        // verify the token and retreive the user from it
        if (!$firebaseUser = $this->getFirebaseUserFromIdToken($data['token']))
            return $this->JsonAbort([
                'status' => 'error',
                'code' => \ErrorCodes::SOCIAL_AUTH_TOKEN_INVALID,
            ], 400);

        // TODO: verify this for potential non exitent email
        // when using facebook login
        $data['email'] = $firebaseUser->email;

        // if user exists login user
        if ($user = User::findByEmail($data['email']))
            return $this->loginByToken($user, $data);

        // else create a new user and a new device
        return $this->signupByToken($data);
    }

    public function loginByToken($user, $data)
    {
        // create a token for the user
        if (!$token = JWTAuth::fromUser($user))
            return $this->JsonAbort([
                'status' => 'error',
                'code' => \ErrorCodes::USER_AUTHENTICATION_FAILED,
            ], 500);

        // upate user ip address
        // and store the token in case it is needed to access the user data later
        $user->update(['token' => $data['token'], 'last_ip_address' => \Request::ip()]);

        $this->JsonAbort([
            'status' => 'success',
            'token' => $token,
            'user' => $user,
        ], 200);
    }

    public function signupByToken($data)
    {
        // validate role_id field
        $this->ValidateOrFail(
            $data,
            [
                'role_id' => 'required|in:' . implode(',', array_keys(Role::ALIAS)),
            ]
        );

        // generate a random password
        // generate a random number to randomize the password
        $time = time();
        $randomNumber = rand() * ($time + $time % rand());

        $data['password'] = hash('sha256', $data['email'] . $randomNumber . $data['token']);
        $data['password_confirmation'] = $data['password'];

        // if username is not present use email
        if (!isset($data['username']))
            $data['username'] = $data['email'];

        // create a user
        $this->ValidateOrFail(
            $data,
            (new User)->rules
        );

        // store current ip address
        $data['created_ip_address'] = $data['last_ip_address'] = \Request::ip();

        Event::fire('rainlab.user.beforeRegister', [&$data]);
        $automaticActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_AUTO;

        // to be used when sending account verification emails
        // $userActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_USER;

        $user = Auth::register($data, $automaticActivation);

        Event::fire('rainlab.user.register', [$user, $data]);

        // create token for the user
        $token = JWTAuth::fromUser($user);


        $this->JsonAbort([
            'status' => 'success',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function deleteAccount()
    {
        $this->retrieveUser();

        extract(
            $this->getValidatedInput(
                ['password'],
                [
                    'password' => 'required|between:8,255',
                ],
            )
        );

        if (!$this->user->checkPassword($password))
            return $this->JsonAbort([
                'status' => 'error',
                'code' => \ErrorCodes::PASSWORD_INCORRECT,
            ], 400);

        $this->user->forceDelete();

        return $this->JsonAbort([
            'status' => 'success',
        ], 200);
    }

    public function getDefaultAvatar()
    {
        $this->retrieveUser();

        $s = DIRECTORY_SEPARATOR;

        if ($this->user->gender == 1)
            $filePath = __DIR__ . "{$s}..{$s}assets{$s}images{$s}profile{$s}avatar_m.png";
        else
            $filePath = __DIR__ . "{$s}..{$s}assets{$s}images{$s}profile{$s}avatar_f.png";

        if (file_exists($filePath)) {
            $fileContent = file_get_contents($filePath);

            $headers = [
                'Content-Type' => 'image/' . (explode('.', $filePath)[1] ?? 'png'),
                'Content-Length' => strlen($fileContent),
                'Cache-Control' => 'public',
            ];

            return response($fileContent, 200, $headers);
        }

        return response('File not found', 404);
    }
}
