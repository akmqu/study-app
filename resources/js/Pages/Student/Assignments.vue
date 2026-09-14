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

/*
|--------------------------------------------------------------------------
| Counts
|--------------------------------------------------------------------------
*/

const todoCount = computed(() => {
    return props.assignments.filter(
        (assignment) =>
            assignment.status === 'todo'
    ).length;
});

const awaitingCount = computed(() => {
    return props.assignments.filter(
        (assignment) =>
            assignment.status ===
            'awaiting_review'
    ).length;
});

const gradedCount = computed(() => {
    return props.assignments.filter(
        (assignment) =>
            assignment.status === 'graded'
    ).length;
});

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const filteredAssignments = computed(() => {
    if (activeFilter.value === 'all') {
        return props.assignments;
    }

    return props.assignments.filter(
        (assignment) =>
            assignment.status ===
            activeFilter.value
    );
});

/*
|--------------------------------------------------------------------------
| Status helpers
|--------------------------------------------------------------------------
*/

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
        return [
            'bg-emerald-50',
            'text-emerald-700',
        ];
    }

    if (status === 'awaiting_review') {
        return [
            'bg-amber-50',
            'text-amber-700',
        ];
    }

    return [
        'bg-indigo-50',
        'text-indigo-700',
    ];
};

/*
|--------------------------------------------------------------------------
| Dates
|--------------------------------------------------------------------------
*/

const formatDate = (value) => {
    if (!value) {
        return 'No deadline';
    }

    return new Intl.DateTimeFormat(
        undefined,
        {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }
    ).format(
        new Date(value)
    );
};

const isOverdue = (assignment) => {
    if (
        assignment.status !== 'todo' ||
        !assignment.deadline
    ) {
        return false;
    }

    return (
        new Date(assignment.deadline) <
        new Date()
    );
};

/*
|--------------------------------------------------------------------------
| File helper
|--------------------------------------------------------------------------
*/

const attachmentLabel = (attachment) => {
    return attachment.name || 'Attachment';
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
                    View homework, deadlines,
                    submissions and tutor feedback.
                </p>
            </div>

            <!-- Stats -->
            <div
                class="grid gap-4 sm:grid-cols-3"
            >
                <!-- Todo -->
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

                <!-- Awaiting review -->
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

            <!-- Assignments -->
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
                                Homework assigned by your
                                tutors.
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

                <!-- Assignment list -->
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
                                <!-- Badges -->
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
                                            isOverdue(
                                                assignment
                                            )
                                        "
                                        class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700"
                                    >
                                        Overdue
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

                                <!-- Title -->
                                <h3
                                    class="mt-3 text-base font-semibold text-slate-900"
                                >
                                    {{
                                        assignment.title
                                    }}
                                </h3>

                                <!-- Tutor -->
                                <p
                                    v-if="
                                        assignment.tutor
                                            ?.name
                                    "
                                    class="mt-1 text-sm text-slate-400"
                                >
                                    From
                                    <span
                                        class="font-medium text-slate-600"
                                    >
                                        {{
                                            assignment
                                                .tutor
                                                .name
                                        }}
                                    </span>
                                </p>

                                <!-- Instructions -->
                                <p
                                    v-if="
                                        assignment.instructions
                                    "
                                    class="mt-3 max-w-3xl whitespace-pre-line text-sm leading-6 text-slate-500"
                                >
                                    {{
                                        assignment.instructions
                                    }}
                                </p>

                                <!-- Attachments -->
                                <div
                                    v-if="
                                        assignment
                                            .attachments
                                            ?.length
                                    "
                                    class="mt-4"
                                >
                                    <p
                                        class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-400"
                                    >
                                        Attachments
                                    </p>

                                    <div
                                        class="flex flex-wrap gap-2"
                                    >
                                        <a
                                            v-for="attachment in assignment.attachments"
                                            :key="
                                                attachment.id ??
                                                attachment.url
                                            "
                                            :href="
                                                attachment.url
                                            "
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700"
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
                                                    d="M21.44 11.05 12.25 20.24a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"
                                                />
                                            </svg>

                                            <span
                                                class="max-w-64 truncate"
                                            >
                                                {{
                                                    attachmentLabel(
                                                        attachment
                                                    )
                                                }}
                                            </span>
                                        </a>
                                    </div>
                                </div>

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
                                        class="mt-1 whitespace-pre-line text-sm text-indigo-900"
                                    >
                                        {{
                                            assignment.feedback
                                        }}
                                    </p>
                                </div>

                                <!-- Deadline -->
                                <div
                                    class="mt-4 flex items-center gap-2 text-xs"
                                    :class="
                                        isOverdue(
                                            assignment
                                        )
                                            ? 'text-red-600'
                                            : 'text-slate-400'
                                    "
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

                                    <span>
                                        Due
                                        {{
                                            formatDate(
                                                assignment.deadline
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>

                            <!-- Status/action -->
                            <div
                                class="shrink-0"
                            >
                                <!-- Submission comes next -->
                                <button
                                    v-if="
                                        assignment.status ===
                                        'todo'
                                    "
                                    type="button"
                                    disabled
                                    class="cursor-not-allowed rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white opacity-50"
                                    title="Submission upload will be connected next"
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