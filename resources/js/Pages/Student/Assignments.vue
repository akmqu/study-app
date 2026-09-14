<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    assignments: {
        type: Array,
        default: () => [],
    },
});

const activeFilter = ref('all');

const filters = [
    {
        key: 'all',
        label: 'All',
    },
    {
        key: 'todo',
        label: 'To do',
    },
    {
        key: 'awaiting_review',
        label: 'Awaiting review',
    },
    {
        key: 'graded',
        label: 'Graded',
    },
];

const todoCount = computed(() =>
    props.assignments.filter(
        (assignment) =>
            assignment.status === 'todo'
    ).length
);

const awaitingCount = computed(() =>
    props.assignments.filter(
        (assignment) =>
            assignment.status === 'awaiting_review'
    ).length
);

const gradedCount = computed(() =>
    props.assignments.filter(
        (assignment) =>
            assignment.status === 'graded'
    ).length
);

const filteredAssignments = computed(() => {
    if (activeFilter.value === 'all') {
        return props.assignments;
    }

    return props.assignments.filter(
        (assignment) =>
            assignment.status === activeFilter.value
    );
});

const statusLabel = (status) => {
    if (status === 'awaiting_review') {
        return 'Awaiting review';
    }

    if (status === 'graded') {
        return 'Graded';
    }

    return 'To do';
};

const statusClasses = (status) => {
    if (status === 'graded') {
        return 'bg-emerald-50 text-emerald-700';
    }

    if (status === 'awaiting_review') {
        return 'bg-amber-50 text-amber-700';
    }

    return 'bg-indigo-50 text-indigo-700';
};

const formatDate = (value) => {
    if (!value) {
        return 'No deadline';
    }

    return new Date(value).toLocaleDateString(
        undefined,
        {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        }
    );
};
</script>

<template>
    <Head title="Assignments" />

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
                    My assignments
                </h1>

                <p
                    class="mt-1 text-sm text-slate-500"
                >
                    View homework, deadlines, submissions
                    and tutor feedback.
                </p>
            </div>

            <!-- Stats -->
            <div
                class="grid gap-4 sm:grid-cols-3"
            >
                <!-- To do -->
                <div
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div
                        class="flex items-center justify-between"
                    >
                        <p
                            class="text-sm font-medium text-slate-500"
                        >
                            To do
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
                        {{ todoCount }}
                    </p>
                </div>

                <!-- Waiting -->
                <div
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div
                        class="flex items-center justify-between"
                    >
                        <p
                            class="text-sm font-medium text-slate-500"
                        >
                            Awaiting review
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
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="9"
                                />

                                <path
                                    stroke-linecap="round"
                                    d="M12 8v4l3 3"
                                />
                            </svg>
                        </span>
                    </div>

                    <p
                        class="mt-3 text-3xl font-semibold text-slate-900"
                    >
                        {{ awaitingCount }}
                    </p>
                </div>

                <!-- Graded -->
                <div
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div
                        class="flex items-center justify-between"
                    >
                        <p
                            class="text-sm font-medium text-slate-500"
                        >
                            Graded
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
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m9 12 2 2 4-4"
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
                        {{ gradedCount }}
                    </p>
                </div>
            </div>

            <!-- Assignment list -->
            <section
                class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <!-- Header -->
                <div
                    class="border-b border-slate-100 px-5 py-4"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
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
                                Keep track of your current
                                and completed homework.
                            </p>
                        </div>

                        <!-- Filters -->
                        <div
                            class="flex flex-wrap gap-2"
                        >
                            <button
                                v-for="filter in filters"
                                :key="filter.key"
                                type="button"
                                class="rounded-lg px-3 py-2 text-xs font-medium transition"
                                :class="
                                    activeFilter ===
                                    filter.key
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                "
                                @click="
                                    activeFilter =
                                        filter.key
                                "
                            >
                                {{ filter.label }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div
                    v-if="
                        filteredAssignments.length ===
                        0
                    "
                    class="flex min-h-80 flex-col items-center justify-center px-6 text-center"
                >
                    <span
                        class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-7 w-7"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M14 2v6h6M9 13h6M9 17h4"
                            />
                        </svg>
                    </span>

                    <h3
                        class="mt-4 text-sm font-medium text-slate-900"
                    >
                        No assignments here yet
                    </h3>

                    <p
                        class="mt-1 max-w-sm text-sm text-slate-500"
                    >
                        Homework from your tutors will
                        appear on this page.
                    </p>
                </div>

                <!-- Assignment cards -->
                <div
                    v-else
                    class="divide-y divide-slate-100"
                >
                    <article
                        v-for="assignment in filteredAssignments"
                        :key="assignment.id"
                        class="p-5 transition hover:bg-slate-50/70"
                    >
                        <div
                            class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between"
                        >
                            <!-- Main -->
                            <div
                                class="min-w-0 flex-1"
                            >
                                <div
                                    class="flex flex-wrap items-center gap-2"
                                >
                                    <span
                                        v-if="
                                            assignment.subject
                                        "
                                        class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600"
                                    >
                                        {{
                                            assignment.subject
                                        }}
                                    </span>

                                    <span
                                        class="rounded-full px-2.5 py-1 text-xs font-medium"
                                        :class="
                                            statusClasses(
                                                assignment.status
                                            )
                                        "
                                    >
                                        {{
                                            statusLabel(
                                                assignment.status
                                            )
                                        }}
                                    </span>

                                    <span
                                        v-if="
                                            assignment.grade !==
                                            null &&
                                            assignment.grade !==
                                            undefined
                                        "
                                        class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700"
                                    >
                                        Grade:
                                        {{
                                            assignment.grade
                                        }}
                                    </span>
                                </div>

                                <h3
                                    class="mt-3 text-base font-semibold text-slate-900"
                                >
                                    {{
                                        assignment.title
                                    }}
                                </h3>

                                <p
                                    v-if="
                                        assignment.instructions
                                    "
                                    class="mt-2 max-w-2xl text-sm leading-6 text-slate-500"
                                >
                                    {{
                                        assignment.instructions
                                    }}
                                </p>

                                <!-- Feedback -->
                                <div
                                    v-if="
                                        assignment.feedback
                                    "
                                    class="mt-4 rounded-lg bg-indigo-50 px-4 py-3"
                                >
                                    <p
                                        class="text-xs font-medium uppercase tracking-wide text-indigo-600"
                                    >
                                        Tutor feedback
                                    </p>

                                    <p
                                        class="mt-1 text-sm text-indigo-900"
                                    >
                                        {{
                                            assignment.feedback
                                        }}
                                    </p>
                                </div>

                                <!-- Deadline -->
                                <div
                                    class="mt-4 flex items-center gap-2 text-xs text-slate-400"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-4 w-4"
                                    >
                                        <rect
                                            width="18"
                                            height="18"
                                            x="3"
                                            y="4"
                                            rx="2"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            d="M16 2v4M8 2v4M3 10h18"
                                        />
                                    </svg>

                                    Due
                                    {{
                                        formatDate(
                                            assignment.deadline
                                        )
                                    }}
                                </div>
                            </div>

                            <!-- Future action -->
                            <div
                                class="shrink-0"
                            >
                                <button
                                    v-if="
                                        assignment.status ===
                                        'todo'
                                    "
                                    type="button"
                                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                                >
                                    Submit work
                                </button>

                                <span
                                    v-else-if="
                                        assignment.status ===
                                        'awaiting_review'
                                    "
                                    class="inline-flex rounded-lg bg-amber-50 px-4 py-2 text-sm font-medium text-amber-700"
                                >
                                    Waiting for review
                                </span>

                                <span
                                    v-else
                                    class="inline-flex rounded-lg bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700"
                                >
                                    Completed
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>