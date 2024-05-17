<?php

namespace App\Providers;

use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        User::class => UsersPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        Gate::before(function ($user, $ability, $models = []) {


            if (empty($models)) {
                return null;
            }
            /**
             * foreach ($models as $model) {
             * //echo $ability. '.' . class_basename($model) . ' <br>';
             * }
             */
            if (class_basename($models[0]) === 'Role' || class_basename($models[0]) === 'AppSettings') {
                return $user->hasRole(Utils::getSuperAdminName()) ? true : null;
            }
        });

        $this->registerPolicies();

        Gate::define('manage-items', 'App\Policies\UsersPolicy@manageItems');

        Gate::define('manage-users', 'App\Policies\UsersPolicy@manageUsers');
    }


}
