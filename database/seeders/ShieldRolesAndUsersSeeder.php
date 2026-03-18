<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ShieldRolesAndUsersSeeder extends Seeder
{
    /**
     * @var array<int, string>
     */
    private const FULL_ACCESS_ROLES = ['super_admin', 'soporte'];

    /**
     * @var array<int, string>
     */
    private const CONTENT_SUBJECTS = [
        'Banner',
        'Campus',
        'Document',
        'Event',
        'Faq',
        'Page',
        'Post',
        'Procedure',
        'Project',
    ];

    /**
     * @var array<int, string>
     */
    private const EDITOR_SUBJECTS = ['Post', 'Event'];

    /**
     * @var array<int, string>
     */
    private const COLLABORATOR_PERMISSIONS = [
        'ViewAny:Post',
        'View:Post',
        'Create:Post',
        'Update:Post',
        'Delete:Post',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Artisan::call('shield:generate', [
            '--all' => true,
            '--option' => 'permissions',
            '--panel' => 'admin',
            '--no-interaction' => true,
        ]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->pluck('name');

        $roles = collect([
            'super_admin',
            'soporte',
            'administrador',
            'editor',
            'colaborador',
        ])->mapWithKeys(fn (string $role): array => [$role => Role::findOrCreate($role, 'web')]);

        foreach (self::FULL_ACCESS_ROLES as $roleName) {
            $roles[$roleName]->syncPermissions($permissions);
        }

        $administradorPermissions = $permissions
            ->filter(fn (string $permission): bool => $this->matchesAnySubject($permission, self::CONTENT_SUBJECTS))
            ->values();

        $roles['administrador']->syncPermissions($administradorPermissions);

        $editorPermissions = $permissions
            ->filter(fn (string $permission): bool => $this->matchesAnySubject($permission, self::EDITOR_SUBJECTS))
            ->values();

        $roles['editor']->syncPermissions($editorPermissions);

        $colaboradorPermissions = collect(self::COLLABORATOR_PERMISSIONS)
            ->filter(fn (string $permission): bool => $permissions->contains($permission))
            ->values();

        $roles['colaborador']->syncPermissions($colaboradorPermissions);

        $this->upsertUserWithRole('Admin', 'admin@iedagropivijay.edu.co', 'super_admin');
        $this->upsertUserWithRole('Soporte Técnico', 'pallaresfj@iedagropivijay.edu.co', 'soporte');
        $this->upsertUserWithRole('Francisco Pallares', 'rectoria@iedagropivijay.edu.co', 'administrador');
        $this->upsertUserWithRole('Editor', 'editor@iedagropivijay.edu.co', 'editor');
        $this->upsertUserWithRole('Colaborador', 'colaborador@iedagropivijay.edu.co', 'colaborador');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function matchesAnySubject(string $permission, array $subjects): bool
    {
        foreach ($subjects as $subject) {
            if (Str::endsWith($permission, ':'.$subject)) {
                return true;
            }
        }

        return false;
    }

    private function upsertUserWithRole(string $name, string $email, string $role): void
    {
        /** @var User|null $user */
        $user = User::query()
            ->where('email', $email)
            ->first();

        if (! $user) {
            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                // Password placeholder: el acceso esperado es via SSO.
                'password' => Hash::make(Str::random(48)),
                'email_verified_at' => now(),
                'is_admin' => in_array($role, ['super_admin', 'soporte'], true),
            ]);
        } else {
            $user->forceFill([
                'name' => $name,
                'is_admin' => in_array($role, ['super_admin', 'soporte'], true),
            ])->save();
        }

        $user->syncRoles([$role]);
    }
}
