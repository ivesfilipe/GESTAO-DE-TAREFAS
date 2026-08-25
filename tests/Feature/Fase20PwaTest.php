<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('manifest webmanifest existe e e valido', function () {
    $path = public_path('manifest.webmanifest');

    expect(file_exists($path))->toBeTrue();

    $manifest = json_decode(file_get_contents($path), true);

    expect($manifest['name'])->toBeString();
    expect($manifest['display'])->toBe('standalone');
    expect($manifest['start_url'])->toBe('/');
    expect(count($manifest['icons']))->toBeGreaterThan(0);
});

test('service worker existe com estrategia de cache', function () {
    $content = file_get_contents(public_path('sw.js'));

    expect($content)->toContain('CACHE_VERSION');
    expect($content)->toContain("'/offline.html'");
    expect($content)->toContain('networkFirstWithOfflineFallback');
});

test('pagina offline existe em portugues', function () {
    $content = file_get_contents(public_path('offline.html'));

    expect($content)->toContain('sem conexão');
});
