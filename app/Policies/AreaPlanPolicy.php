<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AreaPlan;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AreaPlanPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AreaPlan');
    }

    public function view(AuthUser $authUser, AreaPlan $areaPlan): bool
    {
        return $authUser->can('View:AreaPlan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AreaPlan');
    }

    public function update(AuthUser $authUser, AreaPlan $areaPlan): bool
    {
        return $authUser->can('Update:AreaPlan');
    }

    public function delete(AuthUser $authUser, AreaPlan $areaPlan): bool
    {
        return $authUser->can('Delete:AreaPlan');
    }

    public function restore(AuthUser $authUser, AreaPlan $areaPlan): bool
    {
        return $authUser->can('Restore:AreaPlan');
    }

    public function forceDelete(AuthUser $authUser, AreaPlan $areaPlan): bool
    {
        return $authUser->can('ForceDelete:AreaPlan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AreaPlan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AreaPlan');
    }

    public function replicate(AuthUser $authUser, AreaPlan $areaPlan): bool
    {
        return $authUser->can('Replicate:AreaPlan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AreaPlan');
    }
}
