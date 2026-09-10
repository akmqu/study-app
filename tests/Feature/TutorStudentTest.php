<?php

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('tutors can view their own students and only active invitation codes', function () {
    $tutor = User::factory()->tutor()->create();
    $ownStudent = User::factory()->student()->create(['name' => 'Own Student']);
    $otherTutor = User::factory()->tutor()->create();
    $otherStudent = User::factory()->student()->create(['name' => 'Other Student']);

    $tutor->students()->attach($ownStudent->id);
    $otherTutor->students()->attach($otherStudent->id);

    Invitation::query()->create([
        'tutor_id' => $tutor->id,
        'code' => 'ACTIVE01',
        'student_name' => 'Pending Learner',
        'subject' => 'Math',
        'status' => Invitation::STATUS_PENDING,
        'expires_at' => now()->addDay(),
    ]);

    Invitation::query()->create([
        'tutor_id' => $tutor->id,
        'code' => 'USEDCODE',
        'student_name' => 'Joined',
        'subject' => 'Math',
        'status' => Invitation::STATUS_ACCEPTED,
        'student_id' => $ownStudent->id,
        'expires_at' => now()->addDay(),
    ]);

    Invitation::query()->create([
        'tutor_id' => $tutor->id,
        'code' => 'EXPIRED1',
        'student_name' => 'Old',
        'subject' => 'Math',
        'status' => Invitation::STATUS_PENDING,
        'expires_at' => now()->subMinute(),
    ]);

    $response = $this->actingAs($tutor)->get(route('tutor.students'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Tutor/Students')
        ->has('students', 1)
        ->where('students.0.id', $ownStudent->id)
        ->has('invitations', 1)
        ->where('invitations.0.code', 'ACTIVE01'));
});

test('tutors can generate a unique invitation code', function () {
    $tutor = User::factory()->tutor()->create();

    $response = $this->actingAs($tutor)->post(route('tutor.invitations.store'), [
        'student_name' => 'Alice',
        'subject' => 'Physics',
        'price' => '50.00',
    ]);

    $response->assertRedirect(route('tutor.students'));
    $response->assertSessionHas('generated_code');

    $invitation = Invitation::query()->where('tutor_id', $tutor->id)->first();

    expect($invitation)->not->toBeNull()
        ->and($invitation->status)->toBe(Invitation::STATUS_PENDING)
        ->and(strlen($invitation->code))->toBe(8)
        ->and($invitation->expires_at)->not->toBeNull()
        ->and($invitation->student_name)->toBe('Alice')
        ->and($invitation->subject)->toBe('Physics');

    $second = $this->actingAs($tutor)->post(route('tutor.invitations.store'), [
        'student_name' => 'Bob',
        'subject' => 'Chemistry',
    ]);

    $second->assertRedirect(route('tutor.students'));

    $codes = Invitation::query()->where('tutor_id', $tutor->id)->pluck('code');
    expect($codes->unique()->count())->toBe(2);
});

test('students can accept a valid invitation code', function () {
    $tutor = User::factory()->tutor()->create();
    $student = User::factory()->student()->create();

    $invitation = Invitation::query()->create([
        'tutor_id' => $tutor->id,
        'code' => 'ABCD1234',
        'student_name' => 'Learner',
        'subject' => 'Math',
        'status' => Invitation::STATUS_PENDING,
        'expires_at' => now()->addDay(),
    ]);

    $response = $this->actingAs($student)->post(route('student.invitations.redeem'), [
        'code' => '  abcd1234  ',
    ]);

    $response->assertRedirect(route('student.dashboard'));

    $invitation->refresh();

    expect($invitation->status)->toBe(Invitation::STATUS_ACCEPTED)
        ->and($invitation->student_id)->toBe($student->id);

    $this->assertTrue($tutor->students()->where('users.id', $student->id)->exists());
    $this->assertDatabaseCount('tutor_student', 1);
});

test('invitation codes cannot be reused', function () {
    $tutor = User::factory()->tutor()->create();
    $student = User::factory()->student()->create();
    $otherStudent = User::factory()->student()->create();

    Invitation::query()->create([
        'tutor_id' => $tutor->id,
        'code' => 'USEDCODE',
        'student_name' => 'Joined',
        'subject' => 'Math',
        'status' => Invitation::STATUS_ACCEPTED,
        'student_id' => $student->id,
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($otherStudent)->post(route('student.invitations.redeem'), [
        'code' => 'USEDCODE',
    ])->assertSessionHasErrors('code');

    $this->assertFalse($tutor->students()->where('users.id', $otherStudent->id)->exists());
});

test('expired invitation codes cannot be accepted', function () {
    $tutor = User::factory()->tutor()->create();
    $student = User::factory()->student()->create();

    Invitation::query()->create([
        'tutor_id' => $tutor->id,
        'code' => 'EXPIRED1',
        'student_name' => 'Late',
        'subject' => 'Math',
        'status' => Invitation::STATUS_PENDING,
        'expires_at' => now()->subMinute(),
    ]);

    $this->actingAs($student)->post(route('student.invitations.redeem'), [
        'code' => 'EXPIRED1',
    ])->assertSessionHasErrors('code');

    $this->assertFalse($tutor->students()->where('users.id', $student->id)->exists());
});

test('tutors can delete their own unused invitation code', function () {
    $tutor = User::factory()->tutor()->create();

    $invitation = Invitation::query()->create([
        'tutor_id' => $tutor->id,
        'code' => 'DELETEME',
        'student_name' => 'Temp',
        'subject' => 'Math',
        'status' => Invitation::STATUS_PENDING,
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($tutor)
        ->delete(route('tutor.invitations.destroy', $invitation))
        ->assertRedirect(route('tutor.students'));

    $invitation->refresh();

    expect($invitation->status)->toBe(Invitation::STATUS_REVOKED);
});

test('deleted invitation codes cannot be accepted', function () {
    $tutor = User::factory()->tutor()->create();
    $student = User::factory()->student()->create();

    $invitation = Invitation::query()->create([
        'tutor_id' => $tutor->id,
        'code' => 'REVOKED1',
        'student_name' => 'Temp',
        'subject' => 'Math',
        'status' => Invitation::STATUS_PENDING,
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($tutor)->delete(route('tutor.invitations.destroy', $invitation));

    $this->actingAs($student)->post(route('student.invitations.redeem'), [
        'code' => 'REVOKED1',
    ])->assertSessionHasErrors('code');

    $this->assertFalse($tutor->students()->where('users.id', $student->id)->exists());
});

test('tutors cannot delete another tutors invitation code', function () {
    $tutorA = User::factory()->tutor()->create();
    $tutorB = User::factory()->tutor()->create();

    $invitation = Invitation::query()->create([
        'tutor_id' => $tutorA->id,
        'code' => 'OWNCODE1',
        'student_name' => 'Temp',
        'subject' => 'Math',
        'status' => Invitation::STATUS_PENDING,
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($tutorB)
        ->delete(route('tutor.invitations.destroy', $invitation))
        ->assertNotFound();

    $invitation->refresh();
    expect($invitation->status)->toBe(Invitation::STATUS_PENDING);
});

test('a student can belong to multiple tutors via invitation codes', function () {
    $tutorA = User::factory()->tutor()->create();
    $tutorB = User::factory()->tutor()->create();
    $student = User::factory()->student()->create();

    Invitation::query()->create([
        'tutor_id' => $tutorA->id,
        'code' => 'TUTORA01',
        'student_name' => 'Shared',
        'subject' => 'Math',
        'status' => Invitation::STATUS_PENDING,
        'expires_at' => now()->addDay(),
    ]);

    Invitation::query()->create([
        'tutor_id' => $tutorB->id,
        'code' => 'TUTORB01',
        'student_name' => 'Shared',
        'subject' => 'English',
        'status' => Invitation::STATUS_PENDING,
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($student)->post(route('student.invitations.redeem'), [
        'code' => 'TUTORA01',
    ])->assertRedirect(route('student.dashboard'));

    $this->actingAs($student)->post(route('student.invitations.redeem'), [
        'code' => 'TUTORB01',
    ])->assertRedirect(route('student.dashboard'));

    $this->assertTrue($tutorA->students()->where('users.id', $student->id)->exists());
    $this->assertTrue($tutorB->students()->where('users.id', $student->id)->exists());
    $this->assertDatabaseCount('tutor_student', 2);
});

test('removing a student does not delete the user or another tutors relationship', function () {
    $tutorA = User::factory()->tutor()->create();
    $tutorB = User::factory()->tutor()->create();
    $alice = User::factory()->student()->create([
        'email' => 'alice@example.com',
    ]);

    $tutorA->students()->attach($alice->id);
    $tutorB->students()->attach($alice->id);

    $this->actingAs($tutorA)->delete(route('tutor.students.destroy', $alice));

    $this->assertDatabaseHas('users', [
        'id' => $alice->id,
        'email' => 'alice@example.com',
        'role' => 'student',
    ]);
    $this->assertFalse($tutorA->students()->where('users.id', $alice->id)->exists());
    $this->assertTrue($tutorB->students()->where('users.id', $alice->id)->exists());
});

test('email based student linking is no longer available', function () {
    $tutor = User::factory()->tutor()->create();
    $student = User::factory()->student()->create([
        'email' => 'learner@example.com',
    ]);

    $this->actingAs($tutor)
        ->post('/tutor/students', ['email' => 'learner@example.com'])
        ->assertMethodNotAllowed();

    $this->assertFalse($tutor->students()->where('users.id', $student->id)->exists());
});

test('students can refresh their dashboard after a tutor removes the relationship', function () {
    $tutor = User::factory()->tutor()->create();
    $student = User::factory()->student()->create();

    $tutor->students()->attach($student->id);

    $this->actingAs($tutor)->delete(route('tutor.students.destroy', $student));

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Student/Dashboard')
            ->has('tutors', 0));

    $this->actingAs($student)->get(route('tutor.students'))->assertForbidden();
});

test('unauthenticated users cannot access tutor student management', function () {
    $student = User::factory()->student()->create();
    $invitation = Invitation::query()->create([
        'tutor_id' => User::factory()->tutor()->create()->id,
        'code' => 'GUEST001',
        'student_name' => 'Temp',
        'subject' => 'Math',
        'status' => Invitation::STATUS_PENDING,
        'expires_at' => now()->addDay(),
    ]);

    $this->get(route('tutor.students'))->assertRedirect(route('login'));
    $this->post(route('tutor.invitations.store'), [
        'student_name' => 'A',
        'subject' => 'B',
    ])->assertRedirect(route('login'));
    $this->delete(route('tutor.students.destroy', $student))->assertRedirect(route('login'));
    $this->delete(route('tutor.invitations.destroy', $invitation))->assertRedirect(route('login'));
});

test('students cannot access tutor student management', function () {
    $student = User::factory()->student()->create();
    $otherStudent = User::factory()->student()->create();
    $invitation = Invitation::query()->create([
        'tutor_id' => User::factory()->tutor()->create()->id,
        'code' => 'BLOCK001',
        'student_name' => 'Temp',
        'subject' => 'Math',
        'status' => Invitation::STATUS_PENDING,
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($student)->get(route('tutor.students'))->assertForbidden();
    $this->actingAs($student)->post(route('tutor.invitations.store'), [
        'student_name' => 'A',
        'subject' => 'B',
    ])->assertForbidden();
    $this->actingAs($student)->delete(route('tutor.students.destroy', $otherStudent))->assertForbidden();
    $this->actingAs($student)->delete(route('tutor.invitations.destroy', $invitation))->assertForbidden();
});

test('students logging in ignore a stale tutor intended url after unlink flows', function () {
    $student = User::factory()->student()->create();

    $this->get(route('tutor.students'))->assertRedirect(route('login'));

    $this->post('/login', [
        'email' => $student->email,
        'password' => 'password',
    ])->assertRedirect(route('student.dashboard', absolute: false));

    $this->get(route('student.dashboard'))->assertOk();
});

test('tutor_student has unique tutor and student pair constraint', function () {
    $indexes = collect(DB::select(
        'select indexname, indexdef from pg_indexes where tablename = ?',
        ['tutor_student'],
    ));

    expect(
        $indexes->contains(
            fn ($index) => str_contains($index->indexname, 'tutor_id_student_id_unique')
                && str_contains(strtolower($index->indexdef), 'unique')
        )
    )->toBeTrue();
});

test('deleting an accepted invitation is rejected and keeps the relationship', function () {
    $tutor = User::factory()->tutor()->create();
    $student = User::factory()->student()->create();

    $tutor->students()->attach($student->id);

    $invitation = Invitation::query()->create([
        'tutor_id' => $tutor->id,
        'code' => 'ACCEPTED',
        'student_name' => 'Joined',
        'subject' => 'Math',
        'status' => Invitation::STATUS_ACCEPTED,
        'student_id' => $student->id,
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($tutor)
        ->delete(route('tutor.invitations.destroy', $invitation))
        ->assertSessionHasErrors('invitation');

    $this->assertTrue($tutor->students()->where('users.id', $student->id)->exists());
});
