<?php

namespace App\Providers;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Auth::viaRequest('employee-token', function (Request $request) {
            return Employee::where('token', (string) $request->header('x-auth-employee-token'))->first();
        });
    }
}
