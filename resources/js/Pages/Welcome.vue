<script setup>
import {
    Head,
    Link,
    usePage,
} from '@inertiajs/vue3';

import { computed } from 'vue';

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

const page = usePage();

const user = computed(
    () => page.props.auth?.user ?? null
);
</script>

<template>
    <Head title="Tutorly" />

    <div
        class="min-h-screen bg-slate-50 text-slate-900"
    >
        <!-- Header -->
        <header
            class="border-b border-slate-200 bg-white"
        >
            <div
                class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <Link
                    href="/"
                    class="text-base font-semibold tracking-tight text-slate-950"
                >
                    Tutorly
                </Link>

                <nav
                    v-if="canLogin"
                    class="flex items-center gap-2"
                >
                    <Link
                        v-if="user"
                        :href="
                            route(
                                'dashboard'
                            )
                        "
                        class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800"
                    >
                        Dashboard
                    </Link>

                    <template v-else>
                        <Link
                            :href="
                                route(
                                    'login'
                                )
                            "
                            class="rounded-md px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-950"
                        >
                            Log in
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="
                                route(
                                    'register'
                                )
                            "
                            class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800"
                        >
                            Create account
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main>
            <!-- Hero -->
            <section
                class="mx-auto grid max-w-7xl gap-12 px-4 py-16 sm:px-6 sm:py-24 lg:grid-cols-[minmax(0,1fr)_460px] lg:items-center lg:px-8"
            >
                <div>
                    <p
                        class="text-sm font-medium text-slate-500"
                    >
                        Tutor and student workspace
                    </p>

                    <h1
                        class="mt-4 max-w-3xl text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl"
                    >
                        Teaching and learning,
                        organized in one place.
                    </h1>

                    <p
                        class="mt-6 max-w-2xl text-base leading-7 text-slate-600"
                    >
                        Manage lessons, assignments,
                        submissions and feedback without
                        switching between different tools.
                    </p>

                    <div
                        v-if="!user"
                        class="mt-8 flex flex-wrap gap-3"
                    >
                        <Link
                            v-if="canRegister"
                            :href="
                                route(
                                    'register'
                                )
                            "
                            class="rounded-md bg-slate-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800"
                        >
                            Create an account
                        </Link>

                        <Link
                            v-if="canLogin"
                            :href="
                                route(
                                    'login'
                                )
                            "
                            class="rounded-md border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >
                            Log in
                        </Link>
                    </div>

                    <div
                        v-else
                        class="mt-8"
                    >
                        <Link
                            :href="
                                route(
                                    'dashboard'
                                )
                            "
                            class="rounded-md bg-slate-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800"
                        >
                            Continue to dashboard
                        </Link>
                    </div>
                </div>

                <!-- Preview -->
                <div
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="border-b border-slate-200 px-5 py-4"
                    >
                        <p
                            class="text-sm font-semibold text-slate-900"
                        >
                            Today
                        </p>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Everything that needs your attention.
                        </p>
                    </div>

                    <div
                        class="divide-y divide-slate-100"
                    >
                        <div
                            class="px-5 py-4"
                        >
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-slate-400"
                            >
                                Lesson
                            </p>

                            <div
                                class="mt-2 flex items-center justify-between gap-4"
                            >
                                <div>
                                    <p
                                        class="text-sm font-medium text-slate-900"
                                    >
                                        Mathematics
                                    </p>

                                    <p
                                        class="mt-0.5 text-sm text-slate-500"
                                    >
                                        14:30 · 60 minutes
                                    </p>
                                </div>

                                <span
                                    class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600"
                                >
                                    Scheduled
                                </span>
                            </div>
                        </div>

                        <div
                            class="px-5 py-4"
                        >
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-slate-400"
                            >
                                Assignment
                            </p>

                            <div
                                class="mt-2 flex items-center justify-between gap-4"
                            >
                                <div>
                                    <p
                                        class="text-sm font-medium text-slate-900"
                                    >
                                        Fractions worksheet
                                    </p>

                                    <p
                                        class="mt-0.5 text-sm text-slate-500"
                                    >
                                        Submitted for review
                                    </p>
                                </div>

                                <span
                                    class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700"
                                >
                                    Review
                                </span>
                            </div>
                        </div>

                        <div
                            class="px-5 py-4"
                        >
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-slate-400"
                            >
                                Feedback
                            </p>

                            <div
                                class="mt-2 flex items-center justify-between gap-4"
                            >
                                <div>
                                    <p
                                        class="text-sm font-medium text-slate-900"
                                    >
                                        Essay #3
                                    </p>

                                    <p
                                        class="mt-0.5 text-sm text-slate-500"
                                    >
                                        Grade and feedback saved
                                    </p>
                                </div>

                                <span
                                    class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700"
                                >
                                    Graded
                                </span>
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
                    class="mx-auto grid max-w-7xl px-4 py-12 sm:px-6 md:grid-cols-3 lg:px-8"
                >
                    <div
                        class="py-5 md:pr-8"
                    >
                        <h2
                            class="text-base font-semibold text-slate-900"
                        >
                            Lessons
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500"
                        >
                            Schedule lessons and keep your
                            upcoming calendar clear.
                        </p>
                    </div>

                    <div
                        class="border-t border-slate-200 py-5 md:border-l md:border-t-0 md:px-8"
                    >
                        <h2
                            class="text-base font-semibold text-slate-900"
                        >
                            Assignments
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500"
                        >
                            Create homework, submit answers
                            and attach files in one workflow.
                        </p>
                    </div>

                    <div
                        class="border-t border-slate-200 py-5 md:border-l md:border-t-0 md:pl-8"
                    >
                        <h2
                            class="text-base font-semibold text-slate-900"
                        >
                            Feedback
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500"
                        >
                            Review student work, provide
                            feedback and track progress.
                        </p>
                    </div>
                </div>
            </section>
        </main>

        <footer
            class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-8 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8"
        >
            <p>
                Tutorly
            </p>

            <p>
                Laravel {{ laravelVersion }}
                · PHP {{ phpVersion }}
            </p>
        </footer>
    </div>
</template>