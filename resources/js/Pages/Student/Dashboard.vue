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
        route('student.invitations.redeem'),
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
        <div class="mx-auto max-w-5xl">
            <!-- Heading -->
            <header
                class="border-b border-slate-200 pb-6"
            >
                <h1
                    class="text-2xl font-semibold tracking-tight text-slate-950"
                >
                    Home
                </h1>

                <p
                    class="mt-2 text-sm text-slate-500"
                >
                    Welcome back, {{ user.name }}.
                </p>
            </header>

            <!-- Success -->
            <div
                v-if="successMessage"
                class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ successMessage }}
            </div>

            <!-- Assignment summary -->
            <section
                class="mt-8 border-b border-slate-200 pb-8"
            >
                <div
                    class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2
                            class="text-base font-semibold text-slate-900"
                        >
                            Assignments
                        </h2>

                        <p
                            class="mt-2 text-sm text-slate-500"
                        >
                            <span
                                class="font-medium text-slate-700"
                            >
                                {{ upcomingAssignments }}
                            </span>

                            {{
                                upcomingAssignments === 1
                                    ? 'assignment'
                                    : 'assignments'
                            }}
                            to complete

                            <span
                                class="mx-2 text-slate-300"
                            >
                                ·
                            </span>

                            <span
                                class="font-medium text-slate-700"
                            >
                                {{ pendingReviews }}
                            </span>

                            awaiting review
                        </p>
                    </div>

                    <Link
                        :href="
                            route('student.assignments')
                        "
                        class="text-sm font-medium text-slate-700 transition hover:text-slate-950"
                    >
                        View assignments →
                    </Link>
                </div>
            </section>

            <!-- Main content -->
            <div
                class="mt-8 grid gap-12 lg:grid-cols-[minmax(0,1fr)_340px]"
            >
                <!-- Tutors -->
                <section>
                    <div>
                        <h2
                            class="text-base font-semibold text-slate-900"
                        >
                            Your tutors
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Tutors connected to your
                            account.
                        </p>
                    </div>

                    <!-- Empty -->
                    <div
                        v-if="tutors.length === 0"
                        class="mt-5 border-t border-slate-200 py-10"
                    >
                        <p
                            class="text-sm font-medium text-slate-900"
                        >
                            No tutors yet
                        </p>

                        <p
                            class="mt-1 max-w-md text-sm leading-6 text-slate-500"
                        >
                            Ask your tutor for an
                            invitation code and enter it
                            here to connect your account.
                        </p>
                    </div>

                    <!-- Tutor list -->
                    <div
                        v-else
                        class="mt-5 border-t border-slate-200"
                    >
                        <div
                            v-for="tutor in tutors"
                            :key="tutor.id"
                            class="flex items-center justify-between gap-6 border-b border-slate-200 py-4"
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
                                    {{
                                        tutor.subject ||
                                        tutor.email
                                    }}
                                </p>
                            </div>

                            <span
                                v-if="tutor.subject"
                                class="shrink-0 text-sm text-slate-400"
                            >
                                {{ tutor.subject }}
                            </span>
                        </div>
                    </div>
                </section>

                <!-- Join tutor -->
                <section
                    class="self-start border border-slate-200 p-5"
                >
                    <div>
                        <h2
                            class="text-base font-semibold text-slate-900"
                        >
                            Join a tutor
                        </h2>

                        <p
                            class="mt-1 text-sm leading-6 text-slate-500"
                        >
                            Enter the invitation code
                            shared by your tutor.
                        </p>
                    </div>

                    <form
                        class="mt-5"
                        @submit.prevent="submitCode"
                    >
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
                            autocomplete="off"
                            placeholder="ABC12345"
                            class="block w-full rounded-md border-slate-300 bg-white px-3 py-2 font-mono text-sm uppercase tracking-widest placeholder:font-sans placeholder:normal-case placeholder:tracking-normal placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500"
                        />

                        <p
                            v-if="form.errors.code"
                            class="mt-1.5 text-xs text-red-600"
                        >
                            {{ form.errors.code }}
                        </p>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="mt-4 inline-flex w-full items-center justify-center rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-40"
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