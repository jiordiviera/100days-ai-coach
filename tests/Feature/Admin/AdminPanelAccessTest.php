<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to the admin login', function (): void {
    $response = $this->get('/admin');

    $response->assertRedirect('/admin/login');
});

it('forbids non-admin users from accessing the admin panel', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get('/admin')->assertForbidden();
});

it('allows admins to access the dashboard', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    $this->get('/admin')
        ->assertOk()
        ->assertSee('Admin Dashboard', false);
});
