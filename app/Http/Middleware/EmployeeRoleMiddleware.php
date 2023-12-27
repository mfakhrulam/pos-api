<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EmployeeRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $token = $request->header('x-auth-employee-token');

        if(!$token) {
            return response()->json([
                'errors' => [
                    'message' => [
                        'Unauthorized'
                    ]
                ]
            ])->setStatusCode(401);
        }

        $employee = Employee::where('token', $token)->first();

        if(!$employee) {
            return response()->json([
                'errors' => [
                    'message' => [
                        'Unauthorized'
                    ]
                ]
            ])->setStatusCode(401);
        } else if (in_array($employee->role, $roles)) {
            // Auth::guard('employee')->login($employee);
            return $next($request);
        } else {
            return response()->json([
                'errors' => [
                    'message' => [
                        'Insufficient permissions'
                    ]
                ]
            ])->setStatusCode(403);
        }
    }
}
