<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },

    canRegister: {
        type: Boolean,
    },

    laravelVersion: {
        type: String,
        required: true,
    },

    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Tutorly" />

    <div class="min-h-screen bg-slate-50 text-slate-900">
        <!-- Navigation -->
        <header
            class="border-b border-slate-200 bg-white/90 backdrop-blur"
        >
            <div
                class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6"
            >
                <!-- Logo -->
                <Link
                    href="/"
                    class="flex items-center gap-2"
                >
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-white shadow-sm"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m3 10 9-5 9 5-9 5Z"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M7 12v5c3 2 7 2 10 0v-5"
                            />
                        </svg>
                    </span>

                    <span
                        class="text-lg font-semibold tracking-tight"
                    >
                        Tutorly
                    </span>
                </Link>

                <!-- Navigation actions -->
                <nav
                    v-if="canLogin"
                    class="flex items-center gap-2"
                >
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                    >
                        Dashboard
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                        >
                            Log in
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                        >
                            Get started
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero -->
        <main>
            <section
                class="relative overflow-hidden"
            >
                <!-- Background decoration -->
                <div
                    class="pointer-events-none absolute inset-0"
                >
                    <div
                        class="absolute -left-32 top-24 h-80 w-80 rounded-full bg-indigo-100 blur-3xl"
                    ></div>

                    <div
                        class="absolute -right-24 top-8 h-96 w-96 rounded-full bg-violet-100 blur-3xl"
                    ></div>
                </div>

                <div
                    class="relative mx-auto grid max-w-6xl gap-12 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:items-center lg:py-28"
                >
                    <!-- Hero text -->
                    <div>
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700"
                        >
                            <span
                                class="h-2 w-2 rounded-full bg-indigo-500"
                            ></span>

                            Tutoring platform
                        </div>

                        <h1
                            class="mt-6 max-w-xl text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl"
                        >
                            Teaching and learning,
                            organized in one place.
                        </h1>

                        <p
                            class="mt-6 max-w-xl text-base leading-7 text-slate-600 sm:text-lg"
                        >
                            Tutorly helps tutors manage students,
                            homework and feedback while giving
                            students one clear workspace for
                            assignments and progress.
                        </p>

                        <!-- CTA -->
                        <div
                            v-if="!$page.props.auth.user"
                            class="mt-8 flex flex-wrap items-center gap-3"
                        >
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700"
                            >
                                Create an account

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-4 w-4"
                                >
                                    <path
                                        stroke-linecap="round"
                                        d="M5 12h14"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m13 6 6 6-6 6"
                                    />
                                </svg>
                            </Link>

                            <Link
                                v-if="canLogin"
                                :href="route('login')"
                                class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
                            >
                                Log in
                            </Link>
                        </div>

                        <div
                            v-else
                            class="mt-8"
                        >
                            <Link
                                :href="route('dashboard')"
                                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700"
                            >
                                Continue to dashboard

                                <span>→</span>
                            </Link>
                        </div>

                        <!-- Mini benefits -->
                        <div
                            class="mt-10 flex flex-wrap gap-x-6 gap-y-3 text-sm text-slate-500"
                        >
                            <div
                                class="flex items-center gap-2"
                            >
                                <span
                                    class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        class="h-3 w-3"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m5 12 4 4L19 6"
                                        />
                                    </svg>
                                </span>

                                Tutor & student accounts
                            </div>

                            <div
                                class="flex items-center gap-2"
                            >
                                <span
                                    class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        class="h-3 w-3"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m5 12 4 4L19 6"
                                        />
                                    </svg>
                                </span>

                                Homework workflow
                            </div>

                            <div
                                class="flex items-center gap-2"
                            >
                                <span
                                    class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        class="h-3 w-3"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m5 12 4 4L19 6"
                                        />
                                    </svg>
                                </span>

                                Progress tracking
                            </div>
                        </div>
                    </div>

                    <!-- Hero preview -->
                    <div class="relative">
                        <div
                            class="absolute -inset-5 rounded-3xl bg-gradient-to-br from-indigo-200/60 to-violet-200/60 blur-2xl"
                        ></div>

                        <div
                            class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl"
                        >
                            <!-- Fake app header -->
                            <div
                                class="flex h-14 items-center justify-between border-b border-slate-100 px-5"
                            >
                                <div
                                    class="flex items-center gap-2"
                                >
                                    <span
                                        class="flex h-7 w-7 items-center justify-center rounded-md bg-indigo-600 text-white"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-4 w-4"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="m3 10 9-5 9 5-9 5Z"
                                            />
                                        </svg>
                                    </span>

                                    <span
                                        class="text-sm font-semibold text-slate-900"
                                    >
                                        Tutorly
                                    </span>
                                </div>

                                <div
                                    class="flex gap-2"
                                >
                                    <span
                                        class="h-2 w-2 rounded-full bg-slate-200"
                                    ></span>

                                    <span
                                        class="h-2 w-2 rounded-full bg-slate-200"
                                    ></span>

                                    <span
                                        class="h-2 w-2 rounded-full bg-slate-200"
                                    ></span>
                                </div>
                            </div>

                            <!-- Fake dashboard -->
                            <div class="bg-slate-50 p-5">
                                <div>
                                    <div
                                        class="h-3 w-24 rounded bg-indigo-200"
                                    ></div>

                                    <div
                                        class="mt-3 h-6 w-44 rounded bg-slate-800"
                                    ></div>

                                    <div
                                        class="mt-2 h-3 w-64 max-w-full rounded bg-slate-200"
                                    ></div>
                                </div>

                                <!-- Stats -->
                                <div
                                    class="mt-6 grid grid-cols-3 gap-3"
                                >
                                    <div
                                        class="rounded-xl border border-slate-200 bg-white p-4"
                                    >
                                        <div
                                            class="h-3 w-16 rounded bg-slate-200"
                                        ></div>

                                        <div
                                            class="mt-3 text-2xl font-semibold text-slate-900"
                                        >
                                            8
                                        </div>
                                    </div>

                                    <div
                                        class="rounded-xl border border-slate-200 bg-white p-4"
                                    >
                                        <div
                                            class="h-3 w-16 rounded bg-slate-200"
                                        ></div>

                                        <div
                                            class="mt-3 text-2xl font-semibold text-slate-900"
                                        >
                                            3
                                        </div>
                                    </div>

                                    <div
                                        class="rounded-xl border border-slate-200 bg-white p-4"
                                    >
                                        <div
                                            class="h-3 w-16 rounded bg-slate-200"
                                        ></div>

                                        <div
                                            class="mt-3 text-2xl font-semibold text-slate-900"
                                        >
                                            5
                                        </div>
                                    </div>
                                </div>

                                <!-- Dashboard body -->
                                <div
                                    class="mt-4 grid grid-cols-5 gap-3"
                                >
                                    <div
                                        class="col-span-3 rounded-xl border border-slate-200 bg-white p-4"
                                    >
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <div
                                                class="h-3 w-28 rounded bg-slate-300"
                                            ></div>

                                            <div
                                                class="h-5 w-14 rounded-full bg-amber-100"
                                            ></div>
                                        </div>

                                        <div
                                            class="mt-5 space-y-3"
                                        >
                                            <div
                                                class="rounded-lg border border-slate-100 p-3"
                                            >
                                                <div
                                                    class="h-3 w-32 rounded bg-slate-200"
                                                ></div>

                                                <div
                                                    class="mt-2 h-2.5 w-20 rounded bg-slate-100"
                                                ></div>
                                            </div>

                                            <div
                                                class="rounded-lg border border-slate-100 p-3"
                                            >
                                                <div
                                                    class="h-3 w-36 rounded bg-slate-200"
                                                ></div>

                                                <div
                                                    class="mt-2 h-2.5 w-24 rounded bg-slate-100"
                                                ></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="col-span-2 rounded-xl border border-slate-200 bg-white p-4"
                                    >
                                        <div
                                            class="h-3 w-24 rounded bg-slate-300"
                                        ></div>

                                        <div
                                            class="mt-5 space-y-3"
                                        >
                                            <div
                                                class="h-8 rounded-lg bg-slate-100"
                                            ></div>

                                            <div
                                                class="h-8 rounded-lg bg-slate-100"
                                            ></div>

                                            <div
                                                class="h-16 rounded-lg bg-slate-100"
                                            ></div>

                                            <div
                                                class="h-9 rounded-lg bg-indigo-600"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features -->
            <section
                class="border-y border-slate-200 bg-white"
            >
                <div
                    class="mx-auto max-w-6xl px-4 py-20 sm:px-6"
                >
                    <div class="max-w-2xl">
                        <p
                            class="text-sm font-medium text-indigo-600"
                        >
                            One shared workspace
                        </p>

                        <h2
                            class="mt-2 text-3xl font-semibold tracking-tight text-slate-900"
                        >
                            Everything tutors and students need
                            to stay organized.
                        </h2>

                        <p
                            class="mt-3 text-sm leading-6 text-slate-500"
                        >
                            Tutorly keeps everyday tutoring work
                            in one consistent place instead of
                            spreading it across messages, files
                            and separate tools.
                        </p>
                    </div>

                    <div
                        class="mt-10 grid gap-5 md:grid-cols-3"
                    >
                        <!-- Students -->
                        <article
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-6"
                        >
                            <span
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
                                    />

                                    <circle
                                        cx="9"
                                        cy="7"
                                        r="4"
                                    />
                                </svg>
                            </span>

                            <h3
                                class="mt-5 font-semibold text-slate-900"
                            >
                                Manage students
                            </h3>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-500"
                            >
                                Keep student information,
                                private notes and learning
                                activity together.
                            </p>
                        </article>

                        <!-- Homework -->
                        <article
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-6"
                        >
                            <span
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        d="M14 2v6h6M9 13h6M9 17h4"
                                    />
                                </svg>
                            </span>

                            <h3
                                class="mt-5 font-semibold text-slate-900"
                            >
                                Assign homework
                            </h3>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-500"
                            >
                                Create assignments, add
                                instructions, deadlines and
                                files for each student.
                            </p>
                        </article>

                        <!-- Progress -->
                        <article
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-6"
                        >
                            <span
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3 3v18h18"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m7 16 4-5 4 3 5-7"
                                    />
                                </svg>
                            </span>

                            <h3
                                class="mt-5 font-semibold text-slate-900"
                            >
                                Track progress
                            </h3>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-500"
                            >
                                Review submissions, leave
                                feedback and follow learning
                                progress over time.
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <!-- Roles -->
            <section>
                <div
                    class="mx-auto max-w-6xl px-4 py-20 sm:px-6"
                >
                    <div
                        class="grid gap-6 lg:grid-cols-2"
                    >
                        <!-- Tutor -->
                        <article
                            class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm"
                        >
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-600 text-white"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
                                    />

                                    <circle
                                        cx="9"
                                        cy="7"
                                        r="4"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        d="M19 8v6M22 11h-6"
                                    />
                                </svg>
                            </div>

                            <p
                                class="mt-6 text-sm font-medium text-indigo-600"
                            >
                                For tutors
                            </p>

                            <h2
                                class="mt-1 text-2xl font-semibold tracking-tight text-slate-900"
                            >
                                Manage your tutoring work from
                                one dashboard.
                            </h2>

                            <ul
                                class="mt-6 space-y-3 text-sm text-slate-600"
                            >
                                <li
                                    class="flex gap-3"
                                >
                                    <span
                                        class="text-emerald-600"
                                    >
                                        ✓
                                    </span>

                                    Student profiles and private
                                    notes
                                </li>

                                <li
                                    class="flex gap-3"
                                >
                                    <span
                                        class="text-emerald-600"
                                    >
                                        ✓
                                    </span>

                                    Homework assignments and
                                    attachments
                                </li>

                                <li
                                    class="flex gap-3"
                                >
                                    <span
                                        class="text-emerald-600"
                                    >
                                        ✓
                                    </span>

                                    Submissions, grading and
                                    feedback
                                </li>
                            </ul>
                        </article>

                        <!-- Student -->
                        <article
                            class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm"
                        >
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-600 text-white"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m3 10 9-5 9 5-9 5Z"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M7 12v5c3 2 7 2 10 0v-5"
                                    />
                                </svg>
                            </div>

                            <p
                                class="mt-6 text-sm font-medium text-violet-600"
                            >
                                For students
                            </p>

                            <h2
                                class="mt-1 text-2xl font-semibold tracking-tight text-slate-900"
                            >
                                Keep all of your tutoring in one
                                account.
                            </h2>

                            <ul
                                class="mt-6 space-y-3 text-sm text-slate-600"
                            >
                                <li
                                    class="flex gap-3"
                                >
                                    <span
                                        class="text-emerald-600"
                                    >
                                        ✓
                                    </span>

                                    One account for multiple
                                    tutors
                                </li>

                                <li
                                    class="flex gap-3"
                                >
                                    <span
                                        class="text-emerald-600"
                                    >
                                        ✓
                                    </span>

                                    Clear homework and deadlines
                                </li>

                                <li
                                    class="flex gap-3"
                                >
                                    <span
                                        class="text-emerald-600"
                                    >
                                        ✓
                                    </span>

                                    Grades, feedback and progress
                                    in one place
                                </li>
                            </ul>
                        </article>
                    </div>
                </div>
            </section>

            <!-- Final CTA -->
            <section
                v-if="!$page.props.auth.user"
                class="px-4 pb-20 sm:px-6"
            >
                <div
                    class="mx-auto max-w-6xl overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-10 text-center text-white sm:px-10"
                >
                    <h2
                        class="text-2xl font-semibold tracking-tight sm:text-3xl"
                    >
                        Ready to organize your tutoring?
                    </h2>

                    <p
                        class="mx-auto mt-3 max-w-xl text-sm leading-6 text-indigo-100"
                    >
                        Create an account as a tutor or student
                        and start using one shared workspace.
                    </p>

                    <div
                        class="mt-6 flex flex-wrap justify-center gap-3"
                    >
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-xl bg-white px-5 py-3 text-sm font-medium text-indigo-700 transition hover:bg-indigo-50"
                        >
                            Get started
                        </Link>

                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="rounded-xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-medium text-white transition hover:bg-white/20"
                        >
                            Log in
                        </Link>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer
            class="border-t border-slate-200 bg-white"
        >
            <div
                class="mx-auto flex max-w-6xl flex-col gap-3 px-4 py-6 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between sm:px-6"
            >
                <div
                    class="flex items-center gap-2"
                >
                    <span
                        class="flex h-6 w-6 items-center justify-center rounded-md bg-indigo-600 text-white"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-3.5 w-3.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m3 10 9-5 9 5-9 5Z"
                            />
                        </svg>
                    </span>

                    <span class="font-medium text-slate-600">
                        Tutorly
                    </span>
                </div>

                <span>
                    Laravel v{{ laravelVersion }}
                    ·
                    PHP v{{ phpVersion }}
                </span>
            </div>
        </footer>
    </div>
</template>