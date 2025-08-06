<?php

namespace App\Policies;

use App\Models\User;
use App\Models\company;
use Illuminate\Auth\Access\Response;

class CompanyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, company $company): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, company $company): bool
    {
        return $user->id === $company->user_id || $user->hasRole('comptable');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, company $company): bool
    {
        return $user->id === $company->user_id || $user->hasRole('comptable');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, company $company): bool
    {
        return $user->id === $company->user_id || $user->hasRole('comptable');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, company $company): bool
    {
        return $user->id === $company->user_id || $user->hasRole('comptable');
    }

    public function modify(User $user, company $company): Response
    {
        return $user->id === $company->user_id || $user->hasRole('comptable')
            ? Response::allow()
            : Response::deny('You do not have permission to modify this company.');
    }
}
