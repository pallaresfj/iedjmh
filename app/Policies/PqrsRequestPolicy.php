<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PqrsRequest;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PqrsRequestPolicy
{
    use HandlesAuthorization;

    public function before(AuthUser $authUser, string $ability): bool|null
    {
        if (($authUser->is_admin ?? false) === true) {
            return true;
        }

        return null;
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PqrsRequest');
    }

    public function view(AuthUser $authUser, PqrsRequest $pqrsRequest): bool
    {
        return $authUser->can('View:PqrsRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PqrsRequest');
    }

    public function update(AuthUser $authUser, PqrsRequest $pqrsRequest): bool
    {
        return $authUser->can('Update:PqrsRequest');
    }

    public function delete(AuthUser $authUser, PqrsRequest $pqrsRequest): bool
    {
        return $authUser->can('Delete:PqrsRequest');
    }

    public function restore(AuthUser $authUser, PqrsRequest $pqrsRequest): bool
    {
        return $authUser->can('Restore:PqrsRequest');
    }

    public function forceDelete(AuthUser $authUser, PqrsRequest $pqrsRequest): bool
    {
        return $authUser->can('ForceDelete:PqrsRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PqrsRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PqrsRequest');
    }

    public function replicate(AuthUser $authUser, PqrsRequest $pqrsRequest): bool
    {
        return $authUser->can('Replicate:PqrsRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PqrsRequest');
    }
}
