<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaSecurityTest extends TestCase
{
    public function test_manifest_and_required_install_assets_are_valid(): void
    {
        $manifest = json_decode(
            file_get_contents(public_path('manifest.webmanifest')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $this->assertSame('SubaArch ERP', $manifest['name']);
        $this->assertSame('/erp-access?source=pwa', $manifest['start_url']);
        $this->assertSame('standalone', $manifest['display']);

        foreach ([
            'icons/icon-192.png' => [192, 192],
            'icons/icon-512.png' => [512, 512],
            'icons/icon-maskable-512.png' => [512, 512],
        ] as $path => $expectedSize) {
            $fullPath = public_path($path);
            $this->assertFileExists($fullPath);
            $this->assertSame($expectedSize, array_slice(getimagesize($fullPath), 0, 2));
        }
    }

    public function test_service_worker_never_caches_authenticated_pages_or_api_data(): void
    {
        $serviceWorker = file_get_contents(public_path('sw.js'));

        $this->assertStringContainsString("url.pathname.startsWith('/api/')", $serviceWorker);
        $this->assertStringContainsString("url.pathname.startsWith('/erp-access')", $serviceWorker);
        $this->assertStringContainsString("url.pathname.startsWith('/certificate/')", $serviceWorker);
        $this->assertStringContainsString("request.mode === 'navigate'", $serviceWorker);
        $this->assertStringContainsString('fetch(request).catch(() => caches.match(OFFLINE_URL))', $serviceWorker);
        $this->assertStringNotContainsString("caches.match('/api/", $serviceWorker);
    }

    public function test_asset_links_declares_a_valid_android_web_association(): void
    {
        $assetLinks = json_decode(
            file_get_contents(public_path('.well-known/assetlinks.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $statement = $assetLinks[0];
        $fingerprint = $statement['target']['sha256_cert_fingerprints'][0];

        $this->assertContains(
            'delegate_permission/common.handle_all_urls',
            $statement['relation'],
        );
        $this->assertSame('android_app', $statement['target']['namespace']);
        $this->assertNotEmpty($statement['target']['package_name']);
        $this->assertMatchesRegularExpression(
            '/^[0-9A-F]{2}(?::[0-9A-F]{2}){31}$/',
            $fingerprint,
        );
        // Android package and signing fingerprint are tenant/build artifacts.
        // Their exact match is validated in the Android release pipeline, not
        // in the product-agnostic Laravel master workspace.
    }

    public function test_dashboard_exposes_company_hierarchy_and_install_controls_to_every_role(): void
    {
        $blade = file_get_contents(resource_path('views/dashboard.blade.php'));

        $this->assertStringContainsString('id="nav-company-hierarchy"', $blade);
        $this->assertStringContainsString('id="organization-search-input"', $blade);
        $this->assertStringContainsString('id="organization-division-filter"', $blade);
        $this->assertStringContainsString('id="install-app-btn"', $blade);
        $this->assertStringContainsString('rel="manifest"', $blade);
        $this->assertStringNotContainsString('data-target="hierarchy" data-role="admin"', $blade);
    }
}
