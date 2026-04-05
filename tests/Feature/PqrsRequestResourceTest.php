<?php

use App\Filament\Resources\PqrsRequests\Pages\CreatePqrsRequest;
use App\Filament\Resources\PqrsRequests\Pages\EditPqrsRequest;
use App\Filament\Resources\PqrsRequests\Pages\ListPqrsRequests;
use App\Filament\Resources\PqrsRequests\Pages\ViewPqrsRequest;
use App\Filament\Resources\PqrsRequests\PqrsRequestResource;
use App\Filament\Resources\PqrsRequests\RelationManagers\ResponsesRelationManager;
use App\Models\PqrsMessage;
use App\Models\PqrsRequest;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification as FilamentNotification;
use App\Notifications\PqrsResponseNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Filament::setCurrentPanel('admin');
});

test('user with pqrs permissions can list create edit and delete pqrs requests', function () {
    $role = createRoleWithPqrsPermissions('colaborador', ['ViewAny', 'View', 'Create', 'Update', 'Delete']);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $existing = PqrsRequest::query()->create([
        'tracking_code' => 'PQRS-2026-MANAGE-001',
        'type' => 'peticion',
        'is_anonymous' => false,
        'status' => 'received',
        'priority' => 'medium',
        'message' => str_repeat('Contenido existente. ', 3),
        'applicant_name' => 'Solicitante inicial',
        'applicant_email' => 'inicial@example.test',
        'submitted_at' => now()->subHours(2),
    ]);

    $this->actingAs($user)
        ->get(route('filament.admin.resources.pqrs-requests.index'))
        ->assertOk()
        ->assertSee('Contenido existente.');

    Livewire::test(CreatePqrsRequest::class)
        ->fillForm([
            'type' => 'queja',
            'is_anonymous' => false,
            'status' => 'received',
            'priority' => 'high',
            'message' => str_repeat('Mensaje detallado para la solicitud. ', 3),
            'applicant_name' => 'Mariana Torres',
            'applicant_email' => 'mariana@example.test',
            'applicant_phone' => '3000000000',
            'applicant_document' => '111222333',
            'applicant_address' => 'Calle 1 #2-3',
            'consent_habeas_data' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $created = PqrsRequest::query()
        ->where('applicant_email', 'mariana@example.test')
        ->firstOrFail();

    expect($created->tracking_code)->toStartWith('PQRS-'.now()->format('Y').'-')
        ->and($created->created_by)->toBe($user->id)
        ->and($created->updated_by)->toBe($user->id);

    Livewire::test(EditPqrsRequest::class, ['record' => $created->getKey()])
        ->fillForm([
            'status' => 'in_process',
            'internal_notes' => 'En revision por oficina de atencion.',
            'assigned_to' => $user->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $created->refresh();

    expect($created->status)->toBe('in_process')
        ->and($created->internal_notes)->toContain('En revision')
        ->and($created->assigned_to)->toBe($user->id)
        ->and($created->updated_by)->toBe($user->id)
        ->and(PqrsRequestResource::canDelete($created))->toBeTrue();

    Livewire::test(ListPqrsRequests::class)
        ->assertCanSeeTableRecords([$existing, $created])
        ->callTableAction('delete', $created)
        ->assertHasNoTableActionErrors();

    expect(PqrsRequest::query()->whereKey($created->getKey())->exists())->toBeFalse();
});

test('user without pqrs permissions cannot access pqrs resource', function () {
    $role = Role::findOrCreate('colaborador', 'web');
    $role->syncPermissions([]);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $record = PqrsRequest::query()->create([
        'tracking_code' => 'PQRS-2026-BLOCK-001',
        'type' => 'peticion',
        'is_anonymous' => false,
        'status' => 'received',
        'priority' => 'medium',
        'message' => str_repeat('Contenido bloqueado. ', 3),
        'applicant_name' => 'Usuario bloqueado',
        'applicant_email' => 'bloqueado@example.test',
        'submitted_at' => now()->subHour(),
    ]);

    $this->actingAs($user)
        ->get(route('filament.admin.resources.pqrs-requests.index'))
        ->assertForbidden();

    $this->get(route('filament.admin.resources.pqrs-requests.view', ['record' => $record]))
        ->assertForbidden();

    $this->get(route('filament.admin.resources.pqrs-requests.edit', ['record' => $record]))
        ->assertForbidden();

    expect(PqrsRequestResource::canViewAny())->toBeFalse()
        ->and(PqrsRequestResource::canCreate())->toBeFalse()
        ->and(PqrsRequestResource::canView($record))->toBeFalse()
        ->and(PqrsRequestResource::canDelete($record))->toBeFalse();
});

/**
 * @param  array<int, string>  $abilities
 */
function createRoleWithPqrsPermissions(string $roleName, array $abilities): Role
{
    $role = Role::findOrCreate($roleName, 'web');

    $permissions = collect($abilities)
        ->map(fn (string $ability): string => "{$ability}:PqrsRequest")
        ->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'))
        ->all();

    $role->syncPermissions($permissions);

    return $role;
}

test('user with update permission can respond multiple times from pqrs detail action', function () {
    $role = createRoleWithPqrsPermissions('gestor-pqrs-respuestas', ['ViewAny', 'View', 'Update']);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $record = PqrsRequest::query()->create([
        'tracking_code' => 'PQRS-2026-RESP-001',
        'type' => 'peticion',
        'is_anonymous' => false,
        'status' => 'received',
        'priority' => 'medium',
        'message' => str_repeat('Mensaje inicial para historial. ', 3),
        'applicant_name' => 'Solicitante historial',
        'applicant_email' => 'historial@example.test',
        'submitted_at' => now()->subHours(3),
    ]);

    $this->actingAs($user);

    Livewire::test(ViewPqrsRequest::class, ['record' => $record->getKey()])
        ->assertActionVisible('respond')
        ->callAction('respond', data: [
            'responded_at' => now()->subHour(),
            'subject' => "Respuesta al {$record->tracking_code}",
            'message' => '<p>Primera respuesta institucional.</p>',
        ])
        ->assertHasNoActionErrors()
        ->callAction('respond', data: [
            'responded_at' => now(),
            'subject' => "Seguimiento al {$record->tracking_code}",
            'message' => '<p>Segunda respuesta institucional.</p>',
            'reference_url' => 'https://example.test/pqrs/'.$record->tracking_code,
        ])
        ->assertHasNoActionErrors();

    $responses = PqrsMessage::query()
        ->where('pqrs_request_id', $record->id)
        ->where('user_id', $user->id)
        ->orderBy('responded_at')
        ->get();

    expect($responses)->toHaveCount(2)
        ->and($responses->first()?->subject)->toBe("Respuesta al {$record->tracking_code}")
        ->and($responses->first()?->is_internal)->toBeFalse()
        ->and($responses->last()?->subject)->toBe("Seguimiento al {$record->tracking_code}")
        ->and($responses->last()?->reference_url)->toBe('https://example.test/pqrs/'.$record->tracking_code);
});

test('pqrs respond action sends exactly one notification when mail transport is available', function () {
    NotificationFacade::fake();

    $role = createRoleWithPqrsPermissions('gestor-pqrs-mail-ok', ['ViewAny', 'View', 'Update']);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $record = PqrsRequest::query()->create([
        'tracking_code' => 'PQRS-2026-RESP-MAIL-OK',
        'type' => 'peticion',
        'is_anonymous' => false,
        'status' => 'received',
        'priority' => 'medium',
        'message' => str_repeat('Mensaje inicial para correo disponible. ', 3),
        'applicant_name' => 'Solicitante mail ok',
        'applicant_email' => 'mail-ok@example.test',
        'submitted_at' => now()->subHour(),
    ]);

    $this->actingAs($user);

    Livewire::test(ViewPqrsRequest::class, ['record' => $record->getKey()])
        ->callAction('respond', data: [
            'responded_at' => now(),
            'subject' => "Respuesta al {$record->tracking_code}",
            'message' => '<p>Respuesta con correo disponible.</p>',
        ])
        ->assertHasNoActionErrors();

    $response = PqrsMessage::query()
        ->where('pqrs_request_id', $record->id)
        ->where('user_id', $user->id)
        ->first();

    expect($response)->not->toBeNull();

    NotificationFacade::assertSentToTimes($record, PqrsResponseNotification::class, 1);
    $mountedNotifications = new \Filament\Notifications\Livewire\Notifications;
    $mountedNotifications->mount();
    $notificationTitles = $mountedNotifications->notifications
        ->map(fn (FilamentNotification $notification): ?string => $notification->getTitle())
        ->all();

    expect($notificationTitles)
        ->toContain('Respuesta guardada correctamente.')
        ->not->toContain('La respuesta se guardo, pero no se pudo enviar el correo al ciudadano.');
});

test('pqrs respond action keeps response and shows warning when notification cannot be sent', function () {
    NotificationFacade::fake();
    Log::spy();

    $role = createRoleWithPqrsPermissions('gestor-pqrs-mail-fail', ['ViewAny', 'View', 'Update']);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $record = PqrsRequest::query()->create([
        'tracking_code' => 'PQRS-2026-RESP-MAIL-FAIL',
        'type' => 'peticion',
        'is_anonymous' => false,
        'status' => 'received',
        'priority' => 'medium',
        'message' => str_repeat('Mensaje inicial para correo no disponible. ', 3),
        'applicant_name' => 'Solicitante mail fail',
        'applicant_email' => null,
        'submitted_at' => now()->subHour(),
    ]);

    $this->actingAs($user);

    Livewire::test(ViewPqrsRequest::class, ['record' => $record->getKey()])
        ->callAction('respond', data: [
            'responded_at' => now(),
            'subject' => "Respuesta al {$record->tracking_code}",
            'message' => '<p>Respuesta con mailer caido.</p>',
        ])
        ->assertHasNoActionErrors();

    $response = PqrsMessage::query()
        ->where('pqrs_request_id', $record->id)
        ->where('user_id', $user->id)
        ->first();

    expect($response)->not->toBeNull();
    $mountedNotifications = new \Filament\Notifications\Livewire\Notifications;
    $mountedNotifications->mount();
    $notificationTitles = $mountedNotifications->notifications
        ->map(fn (FilamentNotification $notification): ?string => $notification->getTitle())
        ->all();

    expect($notificationTitles)->toContain('La respuesta se guardo, pero no se pudo enviar el correo al ciudadano.');
    Log::shouldHaveReceived('warning')
        ->withArgs(function (string $message, array $context) use ($record, $response): bool {
            return $message === 'pqrs_response_mail_not_sent_missing_email'
                && ($context['tracking_code'] ?? null) === $record->tracking_code
                && ($context['pqrs_message_id'] ?? null) === $response->id;
        })
        ->once();
});

test('user without update permission cannot access pqrs respond action', function () {
    $role = createRoleWithPqrsPermissions('lector-pqrs-respuestas', ['ViewAny', 'View']);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $record = PqrsRequest::query()->create([
        'tracking_code' => 'PQRS-2026-RESP-002',
        'type' => 'peticion',
        'is_anonymous' => false,
        'status' => 'received',
        'priority' => 'medium',
        'message' => str_repeat('Mensaje para validar permisos. ', 3),
        'applicant_name' => 'Usuario lector',
        'applicant_email' => 'lector@example.test',
        'submitted_at' => now()->subHour(),
    ]);

    $this->actingAs($user);

    Livewire::test(ViewPqrsRequest::class, ['record' => $record->getKey()])
        ->assertActionHidden('respond');
});

test('pqrs request form is organized in tabs across create edit and view pages', function () {
    $role = createRoleWithPqrsPermissions('gestor-pqrs-tabs', ['ViewAny', 'View', 'Create', 'Update']);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $record = PqrsRequest::query()->create([
        'tracking_code' => 'PQRS-2026-TABS-001',
        'type' => 'peticion',
        'is_anonymous' => false,
        'status' => 'received',
        'priority' => 'medium',
        'message' => str_repeat('Mensaje para validar pestanas. ', 3),
        'applicant_name' => 'Usuario tabs',
        'applicant_email' => 'tabs@example.test',
        'submitted_at' => now()->subHour(),
    ]);

    $this->actingAs($user);

    Livewire::test(CreatePqrsRequest::class)
        ->assertSee('Radicado y Estado')
        ->assertSee('Solicitante y Solicitud')
        ->assertSee('Gestion Interna');

    Livewire::test(EditPqrsRequest::class, ['record' => $record->getKey()])
        ->assertSee('Radicado y Estado')
        ->assertSee('Solicitante y Solicitud')
        ->assertSee('Gestion Interna');

    Livewire::test(ViewPqrsRequest::class, ['record' => $record->getKey()])
        ->assertSee('Radicado y Estado')
        ->assertSee('Solicitante y Solicitud')
        ->assertSee('Gestion Interna')
        ->assertSee('Respuestas');
});

test('pqrs admin responses relation lists only institutional responses and only on detail page', function () {
    $role = createRoleWithPqrsPermissions('gestor-pqrs-historial', ['ViewAny', 'View']);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $record = PqrsRequest::query()->create([
        'tracking_code' => 'PQRS-2026-HISTORY-001',
        'type' => 'peticion',
        'is_anonymous' => false,
        'status' => 'received',
        'priority' => 'medium',
        'message' => str_repeat('Mensaje para historial en panel. ', 3),
        'applicant_name' => 'Usuario historial',
        'applicant_email' => 'historial-admin@example.test',
        'submitted_at' => now()->subHours(2),
    ]);

    $institutionalResponse = PqrsMessage::query()->create([
        'pqrs_request_id' => $record->id,
        'user_id' => $user->id,
        'author_name' => $user->name,
        'author_email' => $user->email,
        'subject' => "Respuesta al {$record->tracking_code}",
        'message' => '<p>Respuesta institucional visible.</p>',
        'reference_url' => 'https://example.test/soporte/'.$record->tracking_code,
        'responded_at' => now()->subHour(),
        'is_internal' => false,
    ]);

    $citizenMessage = PqrsMessage::query()->create([
        'pqrs_request_id' => $record->id,
        'author_name' => 'Ciudadano',
        'author_email' => $record->applicant_email,
        'subject' => null,
        'message' => 'Mensaje ciudadano que no debe salir en historial admin.',
        'responded_at' => now()->subMinutes(50),
        'is_internal' => false,
    ]);

    $internalMessage = PqrsMessage::query()->create([
        'pqrs_request_id' => $record->id,
        'user_id' => $user->id,
        'author_name' => $user->name,
        'author_email' => $user->email,
        'subject' => 'Nota interna',
        'message' => '<p>No visible por is_internal=true.</p>',
        'responded_at' => now()->subMinutes(40),
        'is_internal' => true,
    ]);

    $this->actingAs($user);

    expect(ResponsesRelationManager::canViewForRecord($record, ViewPqrsRequest::class))->toBeTrue()
        ->and(ResponsesRelationManager::canViewForRecord($record, EditPqrsRequest::class))->toBeFalse();

    Livewire::test(ResponsesRelationManager::class, [
        'ownerRecord' => $record,
        'pageClass' => ViewPqrsRequest::class,
    ])
        ->assertCanSeeTableRecords([$institutionalResponse])
        ->assertSee('example.test')
        ->assertCanNotSeeTableRecords([$citizenMessage, $internalMessage]);
});
