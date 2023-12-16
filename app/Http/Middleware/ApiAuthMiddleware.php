<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();
        $parts = explode('|', $bearer);

        if (count($parts) > 1) {
            $bearer = $parts[1]; // Select the part after the pipe symbol
        } else {
            $bearer = $parts[0]; // No pipe symbol found, return the whole string
        }

        if ($token = DB::table('personal_access_tokens')->where('token',hash('sha256',$bearer))->first())
        {
            if ($user = User::find($token->tokenable_id))
            {
                Auth::login($user);
                return $next($request);
            }
        }

        return response()->json([
            'errors' => [
                'message' => [
                    'Unauthorized'
                ]
            ]
        ])->setStatusCode(401);


        // return response()->json([
        //     'success' => false,
        //     'error' => 'Access denied.',
        // ]);

        // $token = $request->header('Authorization');
        // $authenticate = true;

        // if(!$token) {
        //     $authenticate = false;
        // }

        // $user = User::where('token', $token)->first();
        
        // if(!$user) {
        //     $authenticate = false;
        // } else {
        //     Auth::login($user);
        // }

        // if(!$authenticate) {
        //     return response()->json([
        //         'errors' => [
        //             'message' => [
        //                 'Unauthorized'
        //             ]
        //         ]
        //     ])->setStatusCode(401);
        // }

        // return $next($request);
    }
}
