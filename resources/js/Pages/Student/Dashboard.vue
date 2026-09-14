<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    upcomingAssignments: {
        type: Number,
        default: 0,
    },

    paymentStatus: {
        type: String,
        default: 'No payment information',
    },

    pendingReviews: {
        type: Number,
        default: 0,
    },

    tutors: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const successMessage = computed(
    () => page.props.flash?.success ?? null
);

const user = computed(
    () => page.props.auth?.user ?? {}
);

const form = useForm({
    code: '',
});

const submitCode = () => {
    if (form.processing) {
        return;
    }

    form.code = String(form.code ?? '')
        .trim()
        .toUpperCase();

    form.post(
        route('student.invitations.redeem'),
        {
            preserveScroll: true,

            onSuccess: () => {
                form.reset('code');
            },
        }
    );
};

const tutorInitials = (name) => {
    return String(name ?? '')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) =>
            part.charAt(0).toUpperCase()
        )
        .join('');
};
</script>

<template>
    <Head title="Student Dashboard" />

    <AuthenticatedLayout>
        <div
            class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6"
        >
            <!-- Heading -->
            <div class="mb-8">
                <p
                    class="text-sm font-medium text-indigo-600"
                >
                    Student workspace
                </p>

                <h1
                    class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 md:text-3xl"
                >
                    Welcome back, {{ user.name }}
                </h1>

                <p
                    class="mt-1 text-sm text-slate-500"
                >
                    Keep track of homework, tutors and
                    your learning progress.
                </p>
            </div>

            <!-- Success -->
            <div
                v-if="successMessage"
                class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
            >
                {{ successMessage }}
            </div>

            <!-- Stats -->
            <div
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <!-- Assignments -->
                <div
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div
                        class="flex items-center justify-between"
                    >
                        <p
                            class="text-sm font-medium text-slate-500"
                        >
                            Upcoming assignments
                        </p>

                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
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
                                    d="M14 2v6h6"
                                />
                            </svg>
                        </span>
                    </div>

                    <p
                        class="mt-3 text-3xl font-semibold text-slate-900"
                    >
                        {{ upcomingAssignments }}
                    </p>

                    <Link
                        :href="
                            route('student.assignments')
                        "
                        class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-700"
                    >
                        View assignments

                        <span>→</span>
                    </Link>
                </div>

                <!-- Reviews -->
                <div
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div
                        class="flex items-center justify-between"
                    >
                        <p
                            class="text-sm font-medium text-slate-500"
                        >
                            Pending reviews
                        </p>

                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600"
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
                                    d="M12 8v4l3 3"
                                />
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="9"
                                />
                            </svg>
                        </span>
                    </div>

                    <p
                        class="mt-3 text-3xl font-semibold text-slate-900"
                    >
                        {{ pendingReviews }}
                    </p>

                    <p
                        class="mt-4 text-sm text-slate-400"
                    >
                        Waiting for tutor feedback
                    </p>
                </div>

                <!-- Payments -->
                <div
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div
                        class="flex items-center justify-between"
                    >
                        <p
                            class="text-sm font-medium text-slate-500"
                        >
                            Payment status
                        </p>

                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <rect
                                    width="20"
                                    height="14"
                                    x="2"
                                    y="5"
                                    rx="2"
                                />
                                <path
                                    stroke-linecap="round"
                                    d="M2 10h20"
                                />
                            </svg>
                        </span>
                    </div>

                    <p
                        class="mt-4 text-base font-semibold text-slate-900"
                    >
                        {{ paymentStatus }}
                    </p>

                    <p
                        class="mt-4 text-sm text-slate-400"
                    >
                        Payment information
                    </p>
                </div>
            </div>

            <!-- Main grid -->
            <div
                class="mt-6 grid gap-6 lg:grid-cols-5"
            >
                <!-- Tutors -->
                <section
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-3"
                >
                    <div
                        class="border-b border-slate-100 px-5 py-4"
                    >
                        <h2
                            class="font-medium text-slate-900"
                        >
                            Your tutors
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Tutors currently connected to
                            your account.
                        </p>
                    </div>

                    <!-- No tutors -->
                    <div
                        v-if="tutors.length === 0"
                        class="flex min-h-64 flex-col items-center justify-center px-6 text-center"
                    >
                        <span
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-6 w-6"
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
                        </span>

                        <h3
                            class="mt-4 text-sm font-medium text-slate-900"
                        >
                            No tutors yet
                        </h3>

                        <p
                            class="mt-1 max-w-sm text-sm text-slate-500"
                        >
                            Redeem an invitation code to
                            connect with a tutor.
                        </p>
                    </div>

                    <!-- Tutor list -->
                    <div
                        v-else
                        class="divide-y divide-slate-100"
                    >
                        <div
                            v-for="tutor in tutors"
                            :key="tutor.id"
                            class="flex items-center gap-4 px-5 py-4 transition hover:bg-slate-50/70"
                        >
                            <span
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700"
                            >
                                {{
                                    tutorInitials(
                                        tutor.name
                                    )
                                }}
                            </span>

                            <div
                                class="min-w-0 flex-1"
                            >
                                <p
                                    class="truncate text-sm font-medium text-slate-900"
                                >
                                    {{ tutor.name }}
                                </p>

                                <p
                                    v-if="tutor.subject"
                                    class="mt-0.5 text-sm text-slate-500"
                                >
                                    {{ tutor.subject }}
                                </p>
                            </div>

                            <span
                                class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700"
                            >
                                Active
                            </span>
                        </div>
                    </div>
                </section>

                <!-- Invitation -->
                <section
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2"
                >
                    <div
                        class="flex items-center gap-3"
                    >
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-white"
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
                                    d="M12 5v14M5 12h14"
                                />
                            </svg>
                        </span>

                        <div>
                            <h2
                                class="font-medium text-slate-900"
                            >
                                Join a tutor
                            </h2>

                            <p
                                class="text-sm text-slate-500"
                            >
                                Redeem invitation code
                            </p>
                        </div>
                    </div>

                    <p
                        class="mt-5 text-sm leading-6 text-slate-500"
                    >
                        Enter the invitation code shared
                        by your tutor to connect your
                        account.
                    </p>

                    <form
                        class="mt-5 space-y-4"
                        @submit.prevent="submitCode"
                    >
                        <div>
                            <label
                                for="invitation-code"
                                class="mb-1.5 block text-sm font-medium text-slate-700"
                            >
                                Invitation code
                            </label>

                            <input
                                id="invitation-code"
                                v-model="form.code"
                                type="text"
                                maxlength="8"
                                required
                                placeholder="ABC12345"
                                class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2.5 font-mono text-sm uppercase tracking-widest placeholder:font-sans placeholder:normal-case placeholder:tracking-normal placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                            />

                            <p
                                v-if="form.errors.code"
                                class="mt-1.5 text-xs text-red-600"
                            >
                                {{ form.errors.code }}
                            </p>
                        </div>

                        <button
                            type="submit"
                            :disabled="
                                form.processing
                            "
                            class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? 'Connecting...'
                                    : 'Redeem code'
                            }}
                        </button>
                    </form>
                </section>
            </div>

            <!-- Assignments preview -->
            <section
                class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div
                    class="flex items-center justify-between gap-4"
                >
                    <div>
                        <h2
                            class="font-medium text-slate-900"
                        >
                            Assignments
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Your homework overview.
                        </p>
                    </div>

                    <Link
                        :href="
                            route('student.assignments')
                        "
                        class="text-sm font-medium text-indigo-600 transition hover:text-indigo-700"
                    >
                        View all →
                    </Link>
                </div>

                <div
                    class="mt-5 flex min-h-32 flex-col items-center justify-center rounded-lg border border-dashed border-slate-200 text-center"
                >
                    <p
                        class="text-sm font-medium text-slate-700"
                    >
                        Assignments will appear here
                    </p>

                    <p
                        class="mt-1 text-xs text-slate-400"
                    >
                        We'll connect the real assignment
                        list after the design pass.
                    </p>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>