# Changelog

Progress log for the Tutoring Platform. Newest entries stay at the top.

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
