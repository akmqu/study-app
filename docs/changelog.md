# Changelog

Progress log for the Tutoring Platform. Newest entries stay at the top.

---

## [2026-09-10] Fix Unlink Student 403

### 1. Completed Tasks
1. Moved tutor routes into an explicit `auth` + `role:tutor` group so `DELETE /tutor/students/{student}` is clearly authorized for tutors.
2. Reworked `TutorStudentController@destroy` to authorize by `Auth::id()` link check, return **404** when not linked (not a policy 403), detach the pivot row, and `redirect()->back()` with a success flash for Inertia.
3. Switched `Students.vue` unlink action to `router.delete(route('tutor.students.destroy', student.id))`.
4. Constrained the route parameter with `whereNumber('student')` to avoid ambiguous model/policy binding.

### 2. Modified / Created Files
1. `routes/web.php` — tutor/student groups use explicit `['auth', 'role:…']` middleware.
2. `app/Http/Controllers/TutorStudentController.php` — safer unlink + back redirect.
3. `resources/js/Pages/Tutor/Students.vue` — `router.delete` unlink flow.
4. `tests/Feature/TutorStudentTest.php` — assert back redirect + success flash.

### 3. Next Steps
1. Student profile card with private notes, progress, and direct homework — see [[02_tutor_students]].
2. Optional invite expiry / revoke pending codes.
3. Implement student assignments submit/review flow — see [[06_student_assignments]].
4. Wire payments and calendar modules from [[03_tutor_calendar]] and [[04_tutor_payments]].

---

## [2026-09-10] Invitation Redeem Fix & Unlink Student

### 1. Completed Tasks
1. Hardened invitation redemption: always `strtoupper(trim(...))` before validation/lookup, and match codes with case-insensitive SQL (`UPPER(TRIM(code))`).
2. Simplified the student redeem form so the code is normalized in the UI before POST (no brittle Inertia `transform` chain).
3. Added tutor **Unlink** on the linked-students table to detach a student from `tutor_student`.
4. Covered case-insensitive redeem and unlink (including 404 when not linked) with feature tests.

### 2. Modified / Created Files
1. `app/Models/Invitation.php` — `normalizeCode()`, `findByCode()`, case-insensitive uniqueness checks.
2. `app/Http/Requests/Student/RedeemInvitationRequest.php` — always normalize `code`; lookup via `findByCode()`.
3. `app/Http/Controllers/TutorStudentController.php` — `destroy()` detaches linked student.
4. `routes/web.php` — `DELETE /tutor/students/{student}`.
5. `resources/js/Pages/Tutor/Students.vue` — Unlink action column/button.
6. `resources/js/Pages/Student/Dashboard.vue` — normalize code before redeem POST.
7. `tests/Feature/TutorStudentTest.php` — case-insensitive redeem + unlink tests.

### 3. Next Steps
1. Student profile card with private notes, progress, and direct homework — see [[02_tutor_students]].
2. Optional invite expiry / revoke pending codes.
3. Implement student assignments submit/review flow — see [[06_student_assignments]].
4. Wire payments and calendar modules from [[03_tutor_calendar]] and [[04_tutor_payments]].

---

## [2026-09-10] Invitation Code Linking (Tutor ↔ Student)

### 1. Completed Tasks
1. Extended the `invitations` table with `tutor_id`, unique 8-character `code`, `status` (`pending`/`accepted`), and nullable `student_id`.
2. Replaced email-based instant linking with **Generate Invitation Code** on the tutor Students page.
3. Listed generated invitation codes with status, claimed-by student, and created date alongside linked students.
4. Added student dashboard redemption: enter a code to validate (exists + pending), accept it, and attach the tutor–student pivot.
5. Removed the old add-by-email request/route and updated feature tests for generate + redeem flows.

### 2. Modified / Created Files
1. `database/migrations/2026_09_10_125632_add_fields_to_invitations_table.php` — **created**; invitation columns + FKs.
2. `app/Models/Invitation.php` — fillable fields, relations, pending/accepted helpers, unique code generator.
3. `app/Models/User.php` — `invitations()` hasMany for tutors.
4. `app/Http/Controllers/TutorStudentController.php` — lists students + invitations; `storeInvitation` generates codes.
5. `app/Http/Controllers/StudentController.php` — dashboard shows tutors; `redeemInvitation` claims a code.
6. `app/Http/Requests/Student/RedeemInvitationRequest.php` — **created**; code normalization + pending validation.
7. `app/Http/Requests/Tutor/StoreTutorStudentRequest.php` — **removed** (email linking retired).
8. `routes/web.php` — `POST /tutor/invitations`, `POST /student/invitations/redeem`.
9. `resources/js/Pages/Tutor/Students.vue` — generate button + invitations table.
10. `resources/js/Pages/Student/Dashboard.vue` — redeem form + linked tutors list.
11. `tests/Feature/TutorStudentTest.php` — generate/redeem/invalid/accepted/role tests.

### 3. Next Steps
1. Student profile card with private notes, progress, and direct homework — see [[02_tutor_students]].
2. Optional invite expiry / revoke pending codes.
3. Implement student assignments submit/review flow — see [[06_student_assignments]].
4. Wire payments and calendar modules from [[03_tutor_calendar]] and [[04_tutor_payments]].

---

## [2026-09-10] Tutor Students Management (`02_tutor_students`)

### 1. Completed Tasks
1. Added `tutor_id` and `student_id` foreign keys (with unique pair constraint) to the `tutor_student` pivot table.
2. Wired `User` ↔ students/tutors `belongsToMany` relations and a filled `TutorStudent` model.
3. Created `TutorStudentController` to list the authenticated tutor’s students and add a student by email.
4. Validated add-student requests so the email must belong to an existing `users` row with `role = student`.
5. Registered `GET/POST /tutor/students` under `auth` + `role:tutor`.
6. Built `Tutor/Students.vue` with an add-by-email panel and a responsive students table (dashboard-matched styling).
7. Shared flash `success` for Inertia and linked Dashboard ↔ Students navigation.
8. Added Pest feature coverage for list, add, validation failures, duplicate links, and role forbidden access.

### 2. Modified / Created Files
1. `database/migrations/2026_09_10_124955_add_tutor_and_student_ids_to_tutor_student_table.php` — **created**; pivot FK columns + unique index.
2. `app/Models/TutorStudent.php` — table, fillable, tutor/student relations.
3. `app/Models/User.php` — `students()` / `tutors()` belongsToMany relations.
4. `app/Http/Controllers/TutorStudentController.php` — **created**; `index` + `store`.
5. `app/Http/Requests/Tutor/StoreTutorStudentRequest.php` — **created**; email exists as student.
6. `app/Http/Controllers/TutorController.php` — real active student count; students action moved out.
7. `app/Http/Middleware/HandleInertiaRequests.php` — shared `flash.success`.
8. `routes/web.php` — tutor students GET/POST routes.
9. `resources/js/Pages/Tutor/Students.vue` — **created**; list + add-by-email UI.
10. `resources/js/Pages/Tutor/Dashboard.vue` — Students nav link.
11. `tests/Feature/TutorStudentTest.php` — **created**; feature tests for the flow.

### 3. Next Steps
1. Student profile card with private notes, progress, and direct homework assignment — see [[02_tutor_students]].
2. Invitation link/code flow for students who are not registered yet.
3. Implement student assignments submit/review flow — see [[06_student_assignments]].
4. Wire payments and calendar modules from [[03_tutor_calendar]] and [[04_tutor_payments]].

---

## [2026-09-10] Auth Flow, Role Registration & Dashboard Logout

### 1. Completed Tasks
1. Replaced the stock Laravel Welcome page with a Study App landing page that has working **Log in** and **Register** links/buttons.
2. Fixed registration so the selected role (`student` or `tutor`) is validated, stored on the user, and used for redirect to `/student/dashboard` or `/tutor/dashboard`.
3. Ensured login also redirects by role to the matching dashboard.
4. Added a working **Log Out** control on both tutor and student dashboards (ends the session and returns to `/`).
5. Added role middleware so tutor routes require `tutor` and student routes require `student` (cross-role access returns 403).
6. Updated auth feature tests for registration, login redirects, logout, and role guards (10 tests passing).
7. Rebuilt frontend assets after the Vue page updates.

### 2. Modified / Created Files
1. `resources/js/Pages/Welcome.vue` — Study App landing with Log in / Register CTAs.
2. `app/Models/User.php` — added `role` to fillable attributes.
3. `app/Http/Controllers/Auth/RegisteredUserController.php` — role validation, persistence, and role-based redirect.
4. `app/Http/Controllers/Auth/AuthenticatedSessionController.php` — login redirect by role; logout to `/`.
5. `resources/js/Pages/Auth/Register.vue` — role select posted with the form.
6. `resources/js/Pages/Tutor/Dashboard.vue` — Log Out button (POST `logout`).
7. `resources/js/Pages/Student/Dashboard.vue` — Log Out button (POST `logout`).
8. `app/Http/Middleware/EnsureUserHasRole.php` — **created**; role check middleware.
9. `bootstrap/app.php` — registered `role` middleware alias.
10. `routes/web.php` — `role:tutor` / `role:student` on dashboard route groups.
11. `database/migrations/2026_09_10_115124_add_role_to_users_table.php` — **created**; `users.role` column.
12. `database/factories/UserFactory.php` — default role plus `tutor()` / `student()` states.
13. `tests/Feature/Auth/RegistrationTest.php` — role registration and redirect assertions.
14. `tests/Feature/Auth/AuthenticationTest.php` — role login, logout, and cross-role 403 tests.

### 3. Next Steps
1. Build real tutor students management (list, invite, notes) beyond the stub page — see [[02_tutor_students]].
2. Implement student assignments submit/review flow — see [[06_student_assignments]].
3. Add a shared authenticated layout/nav for role dashboards (profile + consistent logout).
4. Wire payments and calendar modules from [[03_tutor_calendar]] and [[04_tutor_payments]] / [[07_student_payments]].
