<?php

use App\Models\Post;

test('keeps at most three published featured posts when creating a new featured published post', function () {
    $oldest = Post::query()->create([
        'title' => 'Noticia destacada 1',
        'slug' => 'noticia-destacada-1',
        'status' => 'published',
        'is_featured' => true,
        'published_at' => now()->subDays(5),
    ]);

    Post::query()->create([
        'title' => 'Noticia destacada 2',
        'slug' => 'noticia-destacada-2',
        'status' => 'published',
        'is_featured' => true,
        'published_at' => now()->subDays(4),
    ]);

    Post::query()->create([
        'title' => 'Noticia destacada 3',
        'slug' => 'noticia-destacada-3',
        'status' => 'published',
        'is_featured' => true,
        'published_at' => now()->subDays(3),
    ]);

    $newFeatured = Post::query()->create([
        'title' => 'Noticia destacada nueva',
        'slug' => 'noticia-destacada-nueva',
        'status' => 'published',
        'is_featured' => true,
        'published_at' => now(),
    ]);

    expect(Post::query()->where('status', 'published')->where('is_featured', true)->count())->toBe(3);
    expect($newFeatured->fresh()->is_featured)->toBeTrue();
    expect($oldest->fresh()->is_featured)->toBeFalse();
});

test('draft featured posts do not consume the published featured limit', function () {
    foreach (range(1, 3) as $index) {
        Post::query()->create([
            'title' => "Noticia publicada destacada {$index}",
            'slug' => "noticia-publicada-destacada-{$index}",
            'status' => 'published',
            'is_featured' => true,
            'published_at' => now()->subDays(6 - $index),
        ]);
    }

    $draftFeatured = Post::query()->create([
        'title' => 'Noticia borrador destacada',
        'slug' => 'noticia-borrador-destacada',
        'status' => 'draft',
        'is_featured' => true,
    ]);

    expect(Post::query()->where('status', 'published')->where('is_featured', true)->count())->toBe(3);
    expect($draftFeatured->fresh()->is_featured)->toBeTrue();
});

test('publishing a featured draft rotates oldest published featured post', function () {
    $oldest = Post::query()->create([
        'title' => 'Noticia publicada destacada 1',
        'slug' => 'noticia-publicada-destacada-1-publish',
        'status' => 'published',
        'is_featured' => true,
        'published_at' => now()->subDays(8),
    ]);

    Post::query()->create([
        'title' => 'Noticia publicada destacada 2',
        'slug' => 'noticia-publicada-destacada-2-publish',
        'status' => 'published',
        'is_featured' => true,
        'published_at' => now()->subDays(7),
    ]);

    Post::query()->create([
        'title' => 'Noticia publicada destacada 3',
        'slug' => 'noticia-publicada-destacada-3-publish',
        'status' => 'published',
        'is_featured' => true,
        'published_at' => now()->subDays(6),
    ]);

    $draftFeatured = Post::query()->create([
        'title' => 'Noticia borrador a publicar',
        'slug' => 'noticia-borrador-a-publicar',
        'status' => 'draft',
        'is_featured' => true,
    ]);

    $draftFeatured->update([
        'status' => 'published',
        'published_at' => now(),
    ]);

    expect(Post::query()->where('status', 'published')->where('is_featured', true)->count())->toBe(3);
    expect($draftFeatured->fresh()->is_featured)->toBeTrue();
    expect($oldest->fresh()->is_featured)->toBeFalse();
});
