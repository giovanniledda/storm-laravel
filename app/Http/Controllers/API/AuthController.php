<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Validator;

class AuthController extends Controller
{
    protected $signup_rules = [
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required',
        'c_password' => 'required|same:password',
    ];

    protected $login_rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    /**
     * Register (signup/create User) api
     *
     * @return \Illuminate\Http\Response
     */
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), $this->signup_rules);
        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors() as $key => $err) {
                $errors[] = [
                    'status' => 401,
                    'title' => $key,
                    'detail' => $err
                ];
            }
            return response()->json(['errors' => $validator->errors()], 401);  // TODO: rendere jsonapi compliant (il foreach sopra non funziona)
        }
        $input = $request->all();
        $user = User::create($input);

        $token = $user->createAndGetToken();
        $data = [
            'type' => 'token',
            'id' => date('Y-m-dTH:i:s', time()),
            'attributes' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires-in' => 3600
            ]
        ];
        return response()->json(['data' => $data], 200);
    }

    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), $this->login_rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createAndGetToken();
            $data = [
                'type' => 'token',
                'id' => date('Y-m-dTH:i:s', time()),
                'attributes' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires-in' => 3600
                ]
            ];
            return response()->json(['data' => $data], 200);
        } else {
            $error = [
                'id' => date('Y-m-dTH:i:s', time()),
                'status' => 401,
                'title' => 'Unauthorised',
                'detail' => 'You are not authorized to log in.'
            ];
            return response()->json(['errors' => $error], 401);
        }
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();  // $request->user() è l'utente autenticato e loggato
        return response()->json(['data' => []], 204);
    }

    /**
     * Send the email with a link to reset the password
     *
     */
    public function resetPasswordRequest(Request $request)
    {
        // ricevo una richiesta sicura dall'utente

        // invio una mail contenente un link al recupero password
        //
        // se utente ignora, il link scade dopo un tot
        //
        // se utente clicca, trova la classica form per inserimento nuova password

    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        $user = Auth::user(); // $request->user() is the same
         
        $user->getRoleNames();
        $user->getPermissionNames();
        $data = [
            'id' => $user->id,
            'type' => 'users', 
            'attributes' => $user
        ];
        return response()->json(['data' => $data], 200);
    }
}