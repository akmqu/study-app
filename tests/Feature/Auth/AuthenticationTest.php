<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('students can authenticate using the login screen', function () {
    $user = User::factory()->student()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('student.dashboard', absolute: false));
});

test('tutors can authenticate using the login screen', function () {
    $user = User::factory()->tutor()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('tutor.dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('students cannot access tutor dashboard', function () {
    $user = User::factory()->student()->create();

    $response = $this->actingAs($user)->get(route('tutor.dashboard'));

    $response->assertForbidden();
});

test('tutors cannot access student dashboard', function () {
    $user = User::factory()->tutor()->create();

    $response = $this->actingAs($user)->get(route('student.dashboard'));

    $response->assertForbidden();
});
