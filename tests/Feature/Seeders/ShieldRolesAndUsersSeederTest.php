<?php

use App\Models\User;
use Database\Seeders\ShieldRolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('seeder creates base users with deterministic password and roles', function (): void {
    $this->seed(ShieldRolesAndUsersSeeder::class);

    $expectedUsers = [
        'admin@iedagropivijay.edu.co' => 'super_admin',
        'pallaresfj@iedagropivijay.edu.co' => 'soporte',
        'rectoria@iedagropivijay.edu.co' => 'administrador',
        'editor@iedagropivijay.edu.co' => 'editor',
        'colaborador@iedagropivijay.edu.co' => 'colaborador',
    ];

    foreach ($expectedUsers as $email => $role) {
        /** @var User|null $user */
        $user = User::query()->where('email', $email)->first();

        expect($user)->not->toBeNull();
        expect(Hash::check('pass1234', (string) $user?->password))->toBeTrue();
        expect($user?->hasRole($role))->toBeTrue();
    }
});

test('seeder forces password reset for already-existing base users', function (): void {
    $existingUser = User::factory()->create([
        'name' => 'Nombre Antiguo',
        'email' => 'admin@iedagropivijay.edu.co',
        'password' => Hash::make('old-password'),
        'is_admin' => false,
    ]);

    $this->seed(ShieldRolesAndUsersSeeder::class);

    $existingUser->refresh();

    expect($existingUser->name)->toBe('Admin');
    expect($existingUser->is_admin)->toBeTrue();
    expect(Hash::check('pass1234', (string) $existingUser->password))->toBeTrue();
    expect($existingUser->hasRole('super_admin'))->toBeTrue();
});
