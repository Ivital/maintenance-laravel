<?php

declare(strict_types=1);

use Filament\Resources\Resource;
use Liberu\Modules\Maintenance\Core\Filament\Resources\OrganizationResource;

it('disables Filament relationship tenant scoping for maintenance resources with explicit team_id scoping', function (): void {
    $resourceFiles = glob(base_path('modules/module-maintenance-*-filament/src/Resources/*Resource.php')) ?: [];

    expect($resourceFiles)->not->toBeEmpty();

    $checked = 0;

    foreach ($resourceFiles as $file) {
        $contents = file_get_contents($file);

        expect($contents)->not->toBeFalse();

        preg_match('/^namespace\s+([^;]+);/m', (string) $contents, $namespaceMatch);
        preg_match('/^(?:final\s+)?class\s+([A-Za-z_][A-Za-z0-9_]*)\s+extends\s+Resource\b/m', (string) $contents, $classMatch);

        expect($namespaceMatch)->toHaveKey(1);
        expect($classMatch)->toHaveKey(1);

        $resource = $namespaceMatch[1].'\\'.$classMatch[1];

        expect(is_subclass_of($resource, Resource::class))->toBeTrue();

        // Organization has a real team() relationship, so Filament tenancy is valid there.
        if ($resource === OrganizationResource::class) {
            continue;
        }

        expect($resource::isScopedToTenant())
            ->toBeFalse("{$resource} must use its explicit team_id query scope instead of Filament's team() relationship scoping.");

        $queryMethod = new ReflectionMethod($resource, 'getEloquentQuery');

        expect($queryMethod->getDeclaringClass()->getName())
            ->toBe($resource, "{$resource} must explicitly scope its Eloquent query by the current team.");

        $checked++;
    }

    expect($checked)->toBe(19);
});
