<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthRegisterRequest;
use App\Repositories\User\RepositoryUser;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function __construct(protected RepositoryUser $userRepository){}

    public function login(Request $request) {

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {

            $user = Auth::user();

            $token = $user->createToken('efiempresaToken')->accessToken;

            return response()->json(['token' => $token], 200);
        } else {
            return response()->json(['error' => 'Credenciales no válidas'], 401);
        }
    }

    public function register( AuthRegisterRequest $request ) {

        try {

            $validateData = $request->validated();

        } catch (ValidationException $e) {

            $errors = $e->errors();

            return response()->json(['errors' => $errors], 422);
        }

        $user = $this->userRepository->createUser($request);

        $token = $user->createToken('efiempresaToken')->accessToken;

        return response()->json(['token' => $token], 201);
    }

    public function logout(Request $request) {

        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'se ha cerrado la sesión'
        ]);
    }
}
