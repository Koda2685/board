<?php

namespace Tests\Feature;

use Illuminate\Routing\Route;
use Tests\TestCase;

class GovernanceAccessTest extends TestCase
{
    /**
     * @return array<int, string>
     */
    private function middlewareForRoute(string $name): array
    {
        /** @var Route|null $route */
        $route = app('router')->getRoutes()->getByName($name);

        $this->assertNotNull($route, "Route [{$name}] should exist.");

        return $route->gatherMiddleware();
    }

    public function test_governance_document_routes_require_authentication(): void
    {
        foreach (['index', 'store', 'show', 'update', 'destroy'] as $action) {
            $middleware = $this->middlewareForRoute("governance-documents.{$action}");
            $this->assertContains('auth', $middleware);
        }
    }

    public function test_governance_record_routes_require_authentication(): void
    {
        $routeNames = [
            'governance-documents.governance-records.index',
            'governance-documents.governance-records.store',
            'governance-records.show',
            'governance-records.update',
            'governance-records.destroy',
        ];

        foreach ($routeNames as $name) {
            $middleware = $this->middlewareForRoute($name);
            $this->assertContains('auth', $middleware);
        }
    }
}
