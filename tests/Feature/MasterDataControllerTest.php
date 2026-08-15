<?php
uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);
use App\Models\User;

test('master data center index returns 200 for authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/master-demo/masterdatas');

    $response->assertStatus(200);
    $response->assertSee('Master Data Center');
});
