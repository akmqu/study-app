<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new students can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test Student',
        'email' => 'student@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'student',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('student.dashboard', absolute: false));
    $this->assertDatabaseHas('users', [
        'email' => 'student@example.com',
        'role' => 'student',
    ]);
});

test('new tutors can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test Tutor',
        'email' => 'tutor@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'tutor',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('tutor.dashboard', absolute: false));
    $this->assertDatabaseHas('users', [
        'email' => 'tutor@example.com',
        'role' => 'tutor',
    ]);
});
