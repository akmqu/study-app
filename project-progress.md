# Project Progress

Living record of completed work on the Study App (Laravel + Breeze + Inertia/Vue).
Append a new dated entry after each completed prompt/task.

---

## 2026-09-10 — Auth landing, role registration, logout, role guards

### Context reviewed
- Stack: Laravel 13, Breeze (Inertia/Vue), Ziggy, Sail (Postgres).
- Existing pieces: `Register.vue` role select; `RegisteredUserController` role validation/redirect;
  login redirect by role; tutor/student dashboard pages; `users.role` migration already present.
- Gaps found: welcome page was generic Laravel docs UI; `User` model did not mass-assign `role`;
  dashboards had no logout; tutor/student routes had no role middleware; auth feature tests still
  expected generic `/dashboard` redirects.

### Changes made

#### 1. Landing / Welcome page
- Rewrote `resources/js/Pages/Welcome.vue` into a Study App landing page.
- Guest users get header + hero CTAs: **Log in** (`route('login')`) and **Register** / **Create an account** (`route('register')`).
- Authenticated users get **Go to Dashboard** / **Continue to your dashboard** (`route('dashboard')`), which role-routes them.

#### 2. Role registration → DB → role dashboards
- Added `role` to `User` fillable attributes in `app/Models/User.php` (was previously ignored by mass assignment).
- Confirmed `RegisteredUserController@store` validates `role` in `student|tutor`, persists it, logs in, and redirects to `tutor.dashboard` or `student.dashboard`.
- Confirmed `Register.vue` already posts `role` from the select (default `student`).
- Cleaned `database/migrations/2026_09_10_115124_add_role_to_users_table.php` formatting; column already migrated in DB.
- Extended `UserFactory` with default `role: student` plus `tutor()` / `student()` states.

#### 3. Logout on tutor & student dashboards
- Added Inertia `Link` logout buttons (`method="post"` → `route('logout')`) on:
  - `resources/js/Pages/Tutor/Dashboard.vue`
  - `resources/js/Pages/Student/Dashboard.vue`
- `AuthenticatedSessionController@destroy` already invalidates session and redirects to `/`.

#### 4. Routing / middleware / role checks
- Added `app/Http/Middleware/EnsureUserHasRole.php`.
- Registered alias `role` in `bootstrap/app.php`.
- Applied `role:tutor` to `/tutor/*` and `role:student` to `/student/*` in `routes/web.php`.
- Tidied login redirect logic in `AuthenticatedSessionController@store`.
- `/dashboard` still role-redirects tutors → tutor dashboard, others → student dashboard.

#### 5. Tests & verification
- Updated `tests/Feature/Auth/RegistrationTest.php` for student/tutor registration + DB role assertions + role redirects.
- Updated `tests/Feature/Auth/AuthenticationTest.php` for role login redirects, logout → `/`, and cross-role 403s.
- Ran: `./vendor/bin/sail artisan test --filter='RegistrationTest|AuthenticationTest'` → **10 passed**.
- HTTP smoke: POST `/register` with `role=tutor` → 302 `/tutor/dashboard`; logout → 302 `/`.
- Rebuilt frontend assets with `./vendor/bin/sail npm run build`.

#### 6. Tooling / docs
- Installed `laravel/boost` (dev) and ran `boost:install --guidelines --skills --mcp`.
- Created this progress file: `project-progress.md`.

### Current stage
Auth onboarding works end-to-end: landing → register/login with role → correct dashboard → logout back to landing, with role middleware protecting tutor/student areas. Domain features (students list, assignments, payments, calendar) remain stubbed/placeholder per existing docs.
