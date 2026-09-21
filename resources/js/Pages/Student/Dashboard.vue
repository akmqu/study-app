<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import {
    Head,
    Link,
    useForm,
    usePage,
} from '@inertiajs/vue3';

import { computed } from 'vue';

const props = defineProps({
    upcomingAssignments: {
        type: Number,
        default: 0,
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

const user = computed(
    () => page.props.auth?.user ?? {}
);

const successMessage = computed(
    () => page.props.flash?.success ?? null
);

const form = useForm({
    code: '',
});

const submitCode = () => {
    if (form.processing) {
        return;
    }

    form.code = String(
        form.code ?? ''
    )
        .trim()
        .toUpperCase();

    form.post(
        route(
            'student.invitations.redeem'
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                form.reset('code');
            },
        }
    );
};
</script>

<template>
    <Head title="Home" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-6xl">
            <!-- Header -->
            <header>
                <h1
                    class="text-2xl font-semibold tracking-tight text-slate-950"
                >
                    Home
                </h1>

                <p
                    class="mt-1.5 text-sm text-slate-500"
                >
                    Welcome back,
                    {{ user.name }}.
                </p>
            </header>

            <!-- Success -->
            <div
                v-if="successMessage"
                class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ successMessage }}
            </div>

            <!-- Assignment summary -->
            <section
                class="mt-8 rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2
                            class="text-base font-semibold text-slate-900"
                        >
                            Assignments
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Your current homework status.
                        </p>
                    </div>

                    <Link
                        :href="
                            route(
                                'student.assignments'
                            )
                        "
                        class="text-sm font-medium text-slate-600 hover:text-slate-950"
                    >
                        View assignments →
                    </Link>
                </div>

                <div
                    class="grid border-t border-slate-200 sm:grid-cols-2"
                >
                    <div
                        class="px-5 py-5 sm:border-r sm:border-slate-200"
                    >
                        <p
                            class="text-sm text-slate-500"
                        >
                            To complete
                        </p>

                        <div
                            class="mt-2 flex items-baseline gap-2"
                        >
                            <span
                                class="text-2xl font-semibold text-slate-950"
                            >
                                {{
                                    upcomingAssignments
                                }}
                            </span>

                            <span
                                class="text-sm text-slate-500"
                            >
                                {{
                                    upcomingAssignments === 1
                                        ? 'assignment'
                                        : 'assignments'
                                }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="border-t border-slate-200 px-5 py-5 sm:border-t-0"
                    >
                        <p
                            class="text-sm text-slate-500"
                        >
                            Awaiting review
                        </p>

                        <div
                            class="mt-2 flex items-baseline gap-2"
                        >
                            <span
                                class="text-2xl font-semibold text-slate-950"
                            >
                                {{
                                    pendingReviews
                                }}
                            </span>

                            <span
                                class="text-sm text-slate-500"
                            >
                                {{
                                    pendingReviews === 1
                                        ? 'submission'
                                        : 'submissions'
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Bottom grid -->
            <div
                class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]"
            >
                <!-- Tutors -->
                <section
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="border-b border-slate-200 px-5 py-4"
                    >
                        <h2
                            class="text-base font-semibold text-slate-900"
                        >
                            Your tutors
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Tutors connected to your account.
                        </p>
                    </div>

                    <div
                        v-if="
                            tutors.length === 0
                        "
                        class="px-5 py-10"
                    >
                        <p
                            class="text-sm font-medium text-slate-900"
                        >
                            No tutors connected
                        </p>

                        <p
                            class="mt-1 text-sm leading-6 text-slate-500"
                        >
                            Enter an invitation code to connect with your tutor.
                        </p>
                    </div>

                    <div v-else>
                        <div
                            v-for="tutor in tutors"
                            :key="tutor.id"
                            class="flex items-center justify-between gap-5 border-b border-slate-100 px-5 py-4 last:border-b-0"
                        >
                            <div class="min-w-0">
                                <p
                                    class="truncate text-sm font-medium text-slate-900"
                                >
                                    {{ tutor.name }}
                                </p>

                                <p
                                    class="mt-0.5 truncate text-sm text-slate-500"
                                >
                                    {{ tutor.email }}
                                </p>
                            </div>

                            <span
                                v-if="tutor.subject"
                                class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600"
                            >
                                {{ tutor.subject }}
                            </span>
                        </div>
                    </div>
                </section>

                <!-- Join tutor -->
                <section
                    class="self-start rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <h2
                        class="text-base font-semibold text-slate-900"
                    >
                        Join a tutor
                    </h2>

                    <p
                        class="mt-1 text-sm leading-6 text-slate-500"
                    >
                        Enter the invitation code shared by your tutor.
                    </p>

                    <form
                        class="mt-5"
                        @submit.prevent="submitCode"
                    >
                        <label
                            for="invitation-code"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Invitation code
                        </label>

                        <input
                            id="invitation-code"
                            v-model="form.code"
                            type="text"
                            maxlength="8"
                            required
                            autocomplete="off"
                            placeholder="ABC12345"
                            class="mt-2 block w-full rounded-md border-slate-300 bg-white px-3 py-2 font-mono text-sm uppercase tracking-widest placeholder:font-sans placeholder:normal-case placeholder:tracking-normal placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500"
                        />

                        <p
                            v-if="form.errors.code"
                            class="mt-2 text-xs text-red-600"
                        >
                            {{ form.errors.code }}
                        </p>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="mt-4 w-full rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            {{
                                form.processing
                                    ? 'Connecting...'
                                    : 'Connect tutor'
                            }}
                        </button>
                    </form>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>