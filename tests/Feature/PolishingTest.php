<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

/**
 * Structural/file-existence tests - verify project structure is intact.
 * These run without a database connection.
 */
class PolishingTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 2); // two levels up from tests/Feature/
    }

    public function test_ui_components_exist(): void
    {
        $components = [
            'loading.blade.php',
            'alert.blade.php',
            'notification-bell.blade.php',
            'pwa-install-prompt.blade.php',
        ];

        foreach ($components as $component) {
            $path = "{$this->root}/resources/views/components/{$component}";
            $this->assertFileExists($path, "Component {$component} should exist");
        }
    }

    public function test_views_exist(): void
    {
        $views = [
            'trading-plan/index.blade.php',
            'trading-plan/history.blade.php',
            'dashboard.blade.php',
            'dashboard/analytics.blade.php',
            'exports/trading-plan-pdf.blade.php',
            'offline.blade.php',
        ];

        foreach ($views as $view) {
            $path = "{$this->root}/resources/views/{$view}";
            $this->assertFileExists($path, "View {$view} should exist");
        }
    }

    public function test_services_exist(): void
    {
        $services = [
            'AnalyticsService.php',
            'NotificationService.php',
            'TradingCalculatorService.php',
        ];

        foreach ($services as $service) {
            $path = "{$this->root}/app/Services/{$service}";
            $this->assertFileExists($path, "Service {$service} should exist");
        }
    }

    public function test_controllers_exist(): void
    {
        $controllers = [
            'TradingPlanController.php',
            'DashboardController.php',
            'JournalController.php',
            'ChallengeController.php',
            'SettingsController.php',
            'Api/TradingPreviewController.php',
            'Api/NotificationController.php',
            'Api/TradingCalculatorController.php',
        ];

        foreach ($controllers as $controller) {
            $path = "{$this->root}/app/Http/Controllers/{$controller}";
            $this->assertFileExists($path, "Controller {$controller} should exist");
        }
    }

    public function test_routes_configured(): void
    {
        $webRoutes = file_get_contents("{$this->root}/routes/web.php");
        $apiRoutes = file_get_contents("{$this->root}/routes/api.php");

        $expectedWebRoutes = ['trading-plan', 'dashboard', 'export/excel', 'export/pdf'];
        $expectedApiRoutes = ['preview', 'notifications', 'calculate-plan'];

        foreach ($expectedWebRoutes as $route) {
            $this->assertStringContainsString($route, $webRoutes, "Web route {$route} should be configured");
        }

        foreach ($expectedApiRoutes as $route) {
            $this->assertStringContainsString($route, $apiRoutes, "API route {$route} should be configured");
        }
    }

    public function test_bootstrap_integration(): void
    {
        $appCss = file_get_contents("{$this->root}/resources/css/app.css");
        $appJs  = file_get_contents("{$this->root}/resources/js/app.js");

        $this->assertStringContainsString('bootstrap/dist/css/bootstrap.min.css', $appCss);
        $this->assertStringContainsString('bootstrap-icons/font/bootstrap-icons.css', $appCss);
        $this->assertStringContainsString("import * as bootstrap from 'bootstrap'", $appJs);
        $this->assertStringContainsString('window.bootstrap = bootstrap', $appJs);
    }

    public function test_pwa_setup(): void
    {
        $pwaFiles = [
            'public/manifest.json',
            'public/sw.js',
            'resources/views/offline.blade.php',
            'resources/views/components/pwa-install-prompt.blade.php',
        ];

        foreach ($pwaFiles as $file) {
            $this->assertFileExists("{$this->root}/{$file}", "PWA file {$file} should exist");
        }

        $manifest = json_decode(file_get_contents("{$this->root}/public/manifest.json"), true);
        $this->assertIsArray($manifest);
        $this->assertArrayHasKey('name', $manifest);
        $this->assertArrayHasKey('icons', $manifest);
    }

    public function test_migrations_exist(): void
    {
        $migrations = glob("{$this->root}/database/migrations/*.php");
        $this->assertGreaterThanOrEqual(6, count($migrations), 'Should have at least 6 migrations');

        $migrationNames = array_map('basename', $migrations);
        $this->assertNotEmpty(
            array_filter($migrationNames, fn ($f) => str_contains($f, 'add_indexes')),
            'Should have indexes migration'
        );
        $this->assertNotEmpty(
            array_filter($migrationNames, fn ($f) => str_contains($f, 'notifications')),
            'Should have notifications migration'
        );
    }

    public function test_no_debug_files_in_root(): void
    {
        $debugFiles = [
            'audit_api.php',
            'audit_controllers.php',
            'audit_views.php',
            'debug_api.php',
            'quick_test.php',
            'simple_test_app.php',
            'demo.html',
        ];

        foreach ($debugFiles as $file) {
            $this->assertFileDoesNotExist(
                "{$this->root}/{$file}",
                "Debug file {$file} should not exist in production project"
            );
        }
    }

    public function test_no_duplicate_sessions_in_users_migration(): void
    {
        $usersMigration = file_get_contents(
            "{$this->root}/database/migrations/2024_01_01_000001_create_users_table.php"
        );

        // Count how many times sessions table is created
        $count = substr_count($usersMigration, "Schema::create('sessions'");
        $this->assertEquals(0, $count, 'Sessions table should not be created in users migration (use dedicated migration)');
    }

    public function test_notifications_migration_exists(): void
    {
        $migrations = glob("{$this->root}/database/migrations/*.php");
        $hasNotifications = false;
        foreach ($migrations as $m) {
            if (str_contains($m, 'notifications')) {
                $hasNotifications = true;
                break;
            }
        }
        $this->assertTrue($hasNotifications, 'Notifications table migration must exist for database notification channel');
    }
}
