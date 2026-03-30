<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MatriculaRequest;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MatriculaRequestPolicy
{
    use HandlesAuthorization;

    public function before(AuthUser $authUser, string $ability): ?bool
    {
        if (($authUser->is_admin ?? false) === true) {
            return true;
        }

        return null;
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MatriculaRequest');
    }

    public function view(AuthUser $authUser, MatriculaRequest $matriculaRequest): bool
    {
        return $authUser->can('View:MatriculaRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MatriculaRequest');
    }

    public function update(AuthUser $authUser, MatriculaRequest $matriculaRequest): bool
    {
        return $authUser->can('Update:MatriculaRequest');
    }

    public function delete(AuthUser $authUser, MatriculaRequest $matriculaRequest): bool
    {
        return $authUser->can('Delete:MatriculaRequest');
    }

    public function restore(AuthUser $authUser, MatriculaRequest $matriculaRequest): bool
    {
        return $authUser->can('Restore:MatriculaRequest');
    }

    public function forceDelete(AuthUser $authUser, MatriculaRequest $matriculaRequest): bool
    {
        return $authUser->can('ForceDelete:MatriculaRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MatriculaRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MatriculaRequest');
    }

    public function replicate(AuthUser $authUser, MatriculaRequest $matriculaRequest): bool
    {
        return $authUser->can('Replicate:MatriculaRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MatriculaRequest');
    }
}
