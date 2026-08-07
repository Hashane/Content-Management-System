<?php

namespace App\Providers;

use App\Policies\PrivilegePolicy;
use App\Policies\RolePolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PrivilegePolicy::class);

        Response::macro('success', function (mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data,
            ], $code);
        });

        Response::macro('error', function (string $message, int $code = 400, mixed $errors = null): JsonResponse {
            return response()->json([
                'success' => false,
                'message' => $message,
                ...($errors ? ['errors' => $errors] : []),
            ], $code);
        });
    }
}
