<?php

it('redirects guest to login on /admin', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/admin/login');
});

it('can access /admin when authenticated as admin', function () {
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user)->get('/admin');

    $response->assertOk();
});
