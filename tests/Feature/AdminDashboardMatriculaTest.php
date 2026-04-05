<?php

use App\Models\Campus;
use App\Models\MatriculaRequest;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('admin dashboard shows matricula widget empty state', function () {
    $this->actingAs(matriculaDashboardAdmin());

    $this->get('/admin')
        ->assertOk()
        ->assertSee('Solicitudes de Matricula')
        ->assertSee('No hay solicitudes de matricula registradas aun.');
});

test('admin dashboard shows pending matricula kpi and recent matricula items', function () {
    $this->actingAs(matriculaDashboardAdmin());

    $campus = Campus::factory()->create(['name' => 'Sede Principal']);

    MatriculaRequest::factory()->create([
        'student_name' => 'Juan Perez',
        'document_number' => '11111111',
        'grade' => 'primero',
        'campus_id' => $campus->id,
        'status' => 'pending',
        'submitted_at' => now()->subMinutes(30),
    ]);

    MatriculaRequest::factory()->create([
        'student_name' => 'Maria Lopez',
        'document_number' => '22222222',
        'grade' => 'sexto',
        'campus_id' => $campus->id,
        'status' => 'pending',
        'submitted_at' => now()->subMinutes(60),
    ]);

    MatriculaRequest::factory()->create([
        'student_name' => 'Carlos Torres',
        'document_number' => '33333333',
        'grade' => 'undecimo',
        'campus_id' => $campus->id,
        'status' => 'approved',
        'submitted_at' => now()->subMinutes(90),
    ]);

    $content = $this->get('/admin')
        ->assertOk()
        ->assertSee('Juan Perez')
        ->assertSee('Maria Lopez')
        ->assertSee(route('filament.admin.resources.matricula-requests.index'), false)
        ->getContent();

    expect($content)->toMatch('/agro-kpi-card__value">\s*2\s*<\/p>\s*<p class="agro-kpi-card__label">Solicitudes pendientes/s');
});

function matriculaDashboardAdmin(): User
{
    $user = User::factory()->create([
        'is_admin' => true,
    ]);

    $user->assignRole(Role::findOrCreate('super_admin', 'web'));

    return $user;
}
