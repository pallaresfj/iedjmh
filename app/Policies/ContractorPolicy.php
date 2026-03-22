<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Contractor;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ContractorPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Contractor');
    }

    public function view(AuthUser $authUser, Contractor $contractor): bool
    {
        return $authUser->can('View:Contractor');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Contractor');
    }

    public function update(AuthUser $authUser, Contractor $contractor): bool
    {
        return $authUser->can('Update:Contractor');
    }

    public function delete(AuthUser $authUser, Contractor $contractor): bool
    {
        return $authUser->can('Delete:Contractor');
    }

    public function restore(AuthUser $authUser, Contractor $contractor): bool
    {
        return $authUser->can('Restore:Contractor');
    }

    public function forceDelete(AuthUser $authUser, Contractor $contractor): bool
    {
        return $authUser->can('ForceDelete:Contractor');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Contractor');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Contractor');
    }

    public function replicate(AuthUser $authUser, Contractor $contractor): bool
    {
        return $authUser->can('Replicate:Contractor');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Contractor');
    }
}
