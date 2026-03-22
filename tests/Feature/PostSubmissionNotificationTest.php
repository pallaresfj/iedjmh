<?php

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostSubmittedByCollaboratorNotification;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Filament::setCurrentPanel('admin');
});

test('colaborador creating a post notifies editor and administrador', function () {
    Notification::fake();

    $collaboratorRole = createRoleWithPostPermissions('colaborador', ['ViewAny', 'View', 'Create']);
    $editorRole = Role::findOrCreate('editor', 'web');
    $adminRole = Role::findOrCreate('administrador', 'web');

    $collaborator = User::factory()->create();
    $collaborator->assignRole($collaboratorRole);

    $editorRecipient = User::factory()->create();
    $editorRecipient->assignRole($editorRole);

    $adminRecipient = User::factory()->create();
    $adminRecipient->assignRole($adminRole);

    $this->actingAs($collaborator);

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Noticia enviada por colaborador',
            'slug' => 'noticia-enviada-por-colaborador',
            'status' => 'draft',
            'sort_order' => 3,
            'is_featured' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $post = Post::query()->where('slug', 'noticia-enviada-por-colaborador')->firstOrFail();

    expect($post->created_by)->toBe($collaborator->id)
        ->and($post->updated_by)->toBe($collaborator->id);

    Notification::assertSentTo(
        [$editorRecipient, $adminRecipient],
        PostSubmittedByCollaboratorNotification::class,
    );

    Notification::assertNotSentTo($collaborator, PostSubmittedByCollaboratorNotification::class);
});

test('editor creating a post does not trigger collaborator submission notifications', function () {
    Notification::fake();

    $editorRole = createRoleWithPostPermissions('editor', ['ViewAny', 'View', 'Create', 'Update']);
    $adminRole = Role::findOrCreate('administrador', 'web');

    $editor = User::factory()->create();
    $editor->assignRole($editorRole);

    $adminRecipient = User::factory()->create();
    $adminRecipient->assignRole($adminRole);

    $this->actingAs($editor);

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Noticia publicada por editor',
            'slug' => 'noticia-publicada-por-editor',
            'status' => 'published',
            'sort_order' => 2,
            'is_featured' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $post = Post::query()->where('slug', 'noticia-publicada-por-editor')->firstOrFail();

    expect($post->published_at)->not->toBeNull()
        ->and($post->created_by)->toBe($editor->id)
        ->and($post->updated_by)->toBe($editor->id);

    Notification::assertNothingSent();
});

test('editing a post to published status sets updated_by and published_at automatically', function () {
    $editorRole = createRoleWithPostPermissions('editor', ['ViewAny', 'View', 'Create', 'Update']);
    $editor = User::factory()->create();
    $editor->assignRole($editorRole);

    $post = Post::query()->create([
        'title' => 'Noticia para editar',
        'slug' => 'noticia-para-editar',
        'status' => 'draft',
        'published_at' => null,
        'sort_order' => 1,
        'is_featured' => false,
    ]);

    $this->actingAs($editor);

    Livewire::test(EditPost::class, ['record' => $post->getKey()])
        ->fillForm([
            'status' => 'published',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $post->refresh();

    expect($post->updated_by)->toBe($editor->id)
        ->and($post->published_at)->not->toBeNull();
});

test('post submission notification provides mail and database payload', function () {
    $collaborator = User::factory()->create(['name' => 'Colaborador JMH']);
    $recipient = User::factory()->create();

    $post = Post::query()->create([
        'title' => 'Noticia institucional',
        'slug' => 'noticia-institucional',
        'status' => 'draft',
        'sort_order' => 0,
    ]);

    $notification = new PostSubmittedByCollaboratorNotification($post, $collaborator);
    $databasePayload = $notification->toDatabase($recipient);
    $mailPayload = $notification->toMail($recipient);

    expect($databasePayload)->toBeArray()
        ->and(json_encode($databasePayload))->toContain('Noticia institucional')
        ->and($mailPayload->subject)->toContain('Nueva noticia enviada por colaborador')
        ->and($mailPayload->actionUrl)->toContain('/admin/posts/');
});

test('post submission notification prioritizes database channel before mail', function () {
    $collaborator = User::factory()->create();
    $recipient = User::factory()->create();

    $post = Post::query()->create([
        'title' => 'Canales de notificacion',
        'slug' => 'canales-de-notificacion',
        'status' => 'draft',
        'sort_order' => 0,
    ]);

    $notification = new PostSubmittedByCollaboratorNotification($post, $collaborator);

    expect($notification->via($recipient))
        ->toBe(['database', 'mail']);
});

test('colaborador creating a post without recipients keeps flow and logs warning', function () {
    Notification::fake();
    Log::spy();

    $collaboratorRole = createRoleWithPostPermissions('colaborador', ['ViewAny', 'View', 'Create']);

    $collaborator = User::factory()->create();
    $collaborator->assignRole($collaboratorRole);

    $this->actingAs($collaborator);

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Noticia sin moderadores',
            'slug' => 'noticia-sin-moderadores',
            'status' => 'draft',
            'sort_order' => 3,
            'is_featured' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $post = Post::query()->where('slug', 'noticia-sin-moderadores')->firstOrFail();

    expect($post->created_by)->toBe($collaborator->id)
        ->and($post->updated_by)->toBe($collaborator->id);

    Notification::assertNothingSent();

    Log::shouldHaveReceived('warning')
        ->once()
        ->withArgs(function (string $message, array $context): bool {
            return $message === 'post_submission_no_recipients'
                && ($context['post_title'] ?? null) === 'Noticia sin moderadores'
                && ($context['target_roles'] ?? null) === ['editor', 'administrador'];
        });
});

/**
 * @param  array<int, string>  $abilities
 */
function createRoleWithPostPermissions(string $roleName, array $abilities): Role
{
    $role = Role::findOrCreate($roleName, 'web');

    $permissions = collect($abilities)
        ->map(fn (string $ability): string => "{$ability}:Post")
        ->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'))
        ->all();

    $role->syncPermissions($permissions);

    return $role;
}
