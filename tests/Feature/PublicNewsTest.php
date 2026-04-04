<?php

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('news page lists published posts and hides draft posts', function () {
    $publishedPost = Post::query()->create([
        'title' => 'Noticia publicada',
        'slug' => 'noticia-publicada',
        'excerpt' => 'Resumen publicado.',
        'content' => 'Contenido publicado.',
        'status' => 'published',
        'published_at' => now(),
    ]);

    Post::query()->create([
        'title' => 'Noticia borrador',
        'slug' => 'noticia-borrador',
        'status' => 'draft',
    ]);

    $this->get(route('noticias.index'))
        ->assertOk()
        ->assertSee($publishedPost->title)
        ->assertDontSee('Noticia borrador')
        ->assertSee(route('noticias.show', ['slug' => $publishedPost->slug]), false);
});

test('news detail renders html content and blocks draft detail access', function () {
    $publishedPost = Post::query()->create([
        'title' => 'Noticia HTML',
        'slug' => 'noticia-html',
        'excerpt' => 'Resumen con formato.',
        'content' => '<p>Parrafo de <strong>prueba</strong>.</p>',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $draftPost = Post::query()->create([
        'title' => 'Noticia no publica',
        'slug' => 'noticia-no-publica',
        'content' => '<p>Contenido no visible</p>',
        'status' => 'draft',
    ]);

    $this->get(route('noticias.show', ['slug' => $publishedPost->slug]))
        ->assertOk()
        ->assertSee($publishedPost->title)
        ->assertSee('<strong>prueba</strong>', false);

    $this->get(route('noticias.show', ['slug' => $draftPost->slug]))
        ->assertNotFound();
});

test('news filters by search category and sort', function () {
    $newsRoot = Category::query()->create([
        'name' => 'Noticias',
        'slug' => 'noticias',
        'status' => 'published',
    ]);

    $agroCategory = Category::query()->create([
        'name' => 'Agro',
        'slug' => 'agro',
        'status' => 'published',
        'parent_id' => $newsRoot->id,
    ]);

    $oldPost = Post::query()->create([
        'title' => 'Noticia antigua',
        'slug' => 'noticia-antigua',
        'excerpt' => 'Resumen antigua',
        'status' => 'published',
        'published_at' => now()->subDays(5),
    ]);
    $oldPost->categories()->attach($agroCategory->id);

    $recentPost = Post::query()->create([
        'title' => 'Noticia reciente',
        'slug' => 'noticia-reciente',
        'excerpt' => 'Resumen reciente',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('noticias.index', ['q' => 'reciente']))
        ->assertOk()
        ->assertSee($recentPost->title)
        ->assertDontSee($oldPost->title);

    $this->get(route('noticias.index', ['category' => 'agro']))
        ->assertOk()
        ->assertSee($oldPost->title)
        ->assertDontSee($recentPost->title);

    $this->get(route('noticias.index', ['sort' => 'oldest']))
        ->assertOk()
        ->assertSeeInOrder([$oldPost->title, $recentPost->title]);
});

test('legacy comunidad route is not available', function () {
    $this->get('/comunidad')->assertNotFound();
});

test('home links to news listing and news detail', function () {
    $post = Post::query()->create([
        'title' => 'Noticia en inicio',
        'slug' => 'noticia-en-inicio',
        'excerpt' => 'Resumen para home.',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('noticias.index'), false)
        ->assertSee(route('noticias.show', ['slug' => $post->slug]), false);
});

test('news page groups featured posts in featured section', function () {
    Post::query()->create([
        'title' => 'Noticia destacada',
        'slug' => 'noticia-destacada',
        'excerpt' => 'Resumen de noticia destacada.',
        'status' => 'published',
        'is_featured' => true,
        'published_at' => now()->subHour(),
    ]);

    Post::query()->create([
        'title' => 'Noticia general',
        'slug' => 'noticia-general',
        'excerpt' => 'Resumen de noticia general.',
        'status' => 'published',
        'is_featured' => false,
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('noticias.index'))
        ->assertOk()
        ->assertSee('Noticias destacadas')
        ->assertSee('Noticia destacada')
        ->assertSee('Noticia general');
});

test('news pagination uses custom public template and keeps query string', function () {
    foreach (range(1, 10) as $index) {
        Post::query()->create([
            'title' => "Boletin institucional {$index}",
            'slug' => "boletin-institucional-{$index}",
            'excerpt' => "Resumen boletin {$index}.",
            'status' => 'published',
            'published_at' => now()->subMinutes($index),
        ]);
    }

    $this->get(route('noticias.index', ['q' => 'boletin']))
        ->assertOk()
        ->assertSee('page=2', false)
        ->assertSee('q=boletin', false)
        ->assertSee('data-public-pagination-link', false);
});

test('news renders public storage image urls for cover image', function () {
    Storage::disk('public')->put('posts/noticia-prueba.jpg', 'contenido');

    Post::query()->create([
        'title' => 'Noticia con imagen',
        'slug' => 'noticia-con-imagen',
        'excerpt' => 'Resumen de noticia con imagen.',
        'status' => 'published',
        'published_at' => now(),
        'cover_image_path' => 'posts/noticia-prueba.jpg',
    ]);

    $this->get(route('noticias.index'))
        ->assertOk()
        ->assertSee('/storage/posts/noticia-prueba.jpg', false);
});
