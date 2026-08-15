<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PortableDataMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'DataMigrationSeeder']);
    }

    public function test_portable_export_and_import_restore_data_with_integrity_check(): void
    {
        $path = storage_path('framework/testing/erp-portable-test.json');
        File::ensureDirectoryExists(dirname($path));
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $originalName = $ceo->name;

        $this->artisan('erp:data-export', ['path' => $path])
            ->assertSuccessful();

        $this->assertFileExists($path);
        $payload = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('suba-arch-erp-portable-data', $payload['format']);
        $this->assertNotEmpty($payload['integrity_sha256']);
        $this->assertArrayHasKey('users', $payload['tables']);
        $this->assertArrayNotHasKey('sessions', $payload['tables']);

        $ceo->forceFill(['name' => 'Nama Sementara'])->save();
        $this->artisan('erp:data-import', ['path' => $path, '--force' => true])
            ->assertSuccessful();

        $this->assertSame(
            $originalName,
            User::query()->where('username', 'ceo')->value('name'),
        );
    }
}
