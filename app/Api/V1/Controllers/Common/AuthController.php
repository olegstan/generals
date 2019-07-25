<?php

namespace App\Api\V1\Controllers\Common;

use App\Api\V1\Response\Response;
use App\Models\Config;
use Auth;
use Validator;
use App\Api\V1\Requests\StartRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use Session;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        return response()->json(self::getLoginFields());
    }

    public function postConfirmCode()
    {

    }

    public function postForgotPassword(StartRequest $request): Response
    {
        $fields = [
            'phone' => 'required|unique:users|max:255',
        ];



    }


    /**
     * Register user.
     *
     * @param StartRequest $request
     * TODO messages
     * @return Response
     */
    public function postRegister(StartRequest $request): Response
    {
        $fields = [
            'name' => 'required|unique:users|max:255',
            'email' => 'required|unique:users|max:255',
            'phone' => 'required|unique:users|max:255',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
        ];

        $arr = $request->all();

        $arr['phone'] = User::preparePhone($arr['phone']);
        $validator = Validator::make($arr, $fields);


        if ($validator->fails())
        {
            return $this->response()->error($validator->errors());
        }

        $item = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'phone' => $request->get('phone'),
            'password' => bcrypt($request->get('password'))
        ]);


        if ($item) {
            return $this->response()->success('Регистрация прошла успешно.');
        } else {
            return $this->response()->error('Не удалось создать запись.');
        }
    }


    /**
     * @return Response
     */
    public function logout(): Response
    {
        Auth::logout();
        return $this->response()->success();
    }

    /**
     * User authorization.
     *
     * @param StartRequest $request
     *
     * @return Response
     */
    public function auth(StartRequest $request): Response
    {
        $requestArr = $request->all();

        $validator = Validator::make($requestArr, [
            'name' => 'required|max:255',
            'password' => 'required',
            //'captcha' => Config::getValue('captcha_auth') ? 'required|captcha' : ''
        ]);

        if ($validator->fails()) {
            return $this->response()->error($validator->errors());
        }

        /**
         * @var User $user
         */
        $user = User::where('name', $requestArr['name'])
            ->whereNull('blocked_at')
            ->get()
            ->first();

        if ($user && Hash::check($requestArr['password'], $user->password)) {

            if (isset($user->blocked_at)) {
                return $this->response()->error(['name' => ['Аккаунт забанен по причине: ' . $user->block_reason]]);
            }
            if (isset($user->deleted_at)) {
                return $this->response()->error(['Аккаунт удален']);
            }

            Auth::loginUsingId($user->id);
            return $this->response()->success();
        }

        $validator->errors()->add('name', 'Неверный логин/пароль');

        return $this->response()->error($validator->errors());
    }
}