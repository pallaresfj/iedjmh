<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Campus;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampusPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Campus');
    }

    public function view(AuthUser $authUser, Campus $campus): bool
    {
        return $authUser->can('View:Campus');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Campus');
    }

    public function update(AuthUser $authUser, Campus $campus): bool
    {
        return $authUser->can('Update:Campus');
    }

    public function delete(AuthUser $authUser, Campus $campus): bool
    {
        return $authUser->can('Delete:Campus');
    }

    public function restore(AuthUser $authUser, Campus $campus): bool
    {
        return $authUser->can('Restore:Campus');
    }

    public function forceDelete(AuthUser $authUser, Campus $campus): bool
    {
        return $authUser->can('ForceDelete:Campus');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Campus');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Campus');
    }

    public function replicate(AuthUser $authUser, Campus $campus): bool
    {
        return $authUser->can('Replicate:Campus');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Campus');
    }

}