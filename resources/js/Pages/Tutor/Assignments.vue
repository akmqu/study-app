<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import {
    Head,
    router,
    usePage,
} from '@inertiajs/vue3';

import {
    computed,
    ref,
} from 'vue';

const props = defineProps({
    assignments: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const successMessage = computed(
    () =>
        page.props.flash?.success
        ?? null
);

const activeFilter = ref('all');
const selectedAssignment = ref(null);
const deletingAssignmentId = ref(null);

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

const filteredAssignments =
    computed(() => {
        if (
            activeFilter.value ===
            'all'
        ) {
            return props.assignments;
        }

        return props.assignments.filter(
            (assignment) =>
                assignment.status ===
                activeFilter.value
        );
    });

const filterCount = (status) => {
    if (status === 'all') {
        return props
            .assignments
            .length;
    }

    return props.assignments.filter(
        (assignment) =>
            assignment.status ===
            status
    ).length;
};

const statusLabel = (status) => {
    if (
        status ===
        'awaiting_review'
    ) {
        return 'Awaiting review';
    }

    if (
        status === 'graded'
    ) {
        return 'Graded';
    }

    return 'To do';
};

const statusClasses = (status) => {
    if (
        status === 'graded'
    ) {
        return [
            'bg-emerald-50',
            'text-emerald-700',
        ];
    }

    if (
        status ===
        'awaiting_review'
    ) {
        return [
            'bg-amber-50',
            'text-amber-700',
        ];
    }

    return [
        'bg-slate-100',
        'text-slate-700',
    ];
};

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
        }
    ).format(
        new Date(value)
    );
};

const formatDateTime = (value) => {
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
        assignment.status !==
            'todo'
        || !assignment.deadline
    ) {
        return false;
    }

    return (
        new Date(
            assignment.deadline
        ) < new Date()
    );
};

const openAssignment = (
    assignment
) => {
    selectedAssignment.value =
        assignment;
};

const closeAssignment = () => {
    if (
        deletingAssignmentId.value
    ) {
        return;
    }

    selectedAssignment.value =
        null;
};

const deleteAssignment = (
    assignment
) => {
    if (
        deletingAssignmentId.value
    ) {
        return;
    }

    const confirmed =
        window.confirm(
            `Delete "${assignment.title}"?`
        );

    if (!confirmed) {
        return;
    }

    deletingAssignmentId.value =
        assignment.id;

    router.delete(
        route(
            'tutor.assignments.destroy',
            assignment.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                selectedAssignment.value =
                    null;
            },

            onFinish: () => {
                deletingAssignmentId.value =
                    null;
            },
        }
    );
};
</script>

<template>
    <Head title="Assignments" />

    <AuthenticatedLayout>
        <div
            class="mx-auto max-w-5xl"
        >
            <!-- Heading -->
            <header
                class="border-b border-slate-200 pb-6"
            >
                <h1
                    class="text-2xl font-semibold tracking-tight text-slate-950"
                >
                    Assignments
                </h1>

                <p
                    class="mt-2 text-sm text-slate-500"
                >
                    Homework assigned to
                    your students.
                </p>
            </header>

            <!-- Success -->
            <div
                v-if="successMessage"
                class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ successMessage }}
            </div>

            <!-- Filters -->
            <div
                class="mt-6 flex flex-wrap gap-1 border-b border-slate-200"
            >
                <button
                    v-for="filter in filters"
                    :key="filter.key"
                    type="button"
                    class="-mb-px border-b-2 px-3 py-3 text-sm font-medium transition"
                    :class="
                        activeFilter ===
                        filter.key
                            ? 'border-slate-900 text-slate-950'
                            : 'border-transparent text-slate-500 hover:text-slate-900'
                    "
                    @click="
                        activeFilter =
                            filter.key
                    "
                >
                    {{ filter.label }}

                    <span
                        class="ml-1.5 text-xs text-slate-400"
                    >
                        {{
                            filterCount(
                                filter.key
                            )
                        }}
                    </span>
                </button>
            </div>

            <!-- Empty -->
            <div
                v-if="
                    filteredAssignments.length
                    === 0
                "
                class="py-14"
            >
                <p
                    class="text-sm font-medium text-slate-900"
                >
                    No assignments here
                </p>

                <p
                    class="mt-1 text-sm text-slate-500"
                >
                    There are no assignments
                    in this category.
                </p>
            </div>

            <!-- List -->
            <div
                v-else
                class="border-b border-slate-200"
            >
                <button
                    v-for="assignment in filteredAssignments"
                    :key="assignment.id"
                    type="button"
                    class="group grid w-full gap-3 border-b border-slate-200 py-5 text-left transition last:border-b-0 hover:bg-slate-50 sm:grid-cols-[minmax(0,1fr)_160px_150px_32px] sm:items-center sm:px-3"
                    @click="
                        openAssignment(
                            assignment
                        )
                    "
                >
                    <!-- Assignment -->
                    <div
                        class="min-w-0"
                    >
                        <div
                            class="flex flex-wrap items-center gap-2"
                        >
                            <p
                                class="truncate text-sm font-medium text-slate-900"
                            >
                                {{
                                    assignment.title
                                }}
                            </p>

                            <span
                                v-if="
                                    isOverdue(
                                        assignment
                                    )
                                "
                                class="text-xs font-medium text-red-600"
                            >
                                Overdue
                            </span>
                        </div>

                        <p
                            class="mt-1 truncate text-sm text-slate-500"
                        >
                            {{
                                assignment
                                    .student
                                    ?.name
                                || 'Unknown student'
                            }}

                            <span
                                v-if="
                                    assignment.subject
                                "
                                class="mx-1.5 text-slate-300"
                            >
                                ·
                            </span>

                            <span
                                v-if="
                                    assignment.subject
                                "
                            >
                                {{
                                    assignment.subject
                                }}
                            </span>
                        </p>
                    </div>

                    <!-- Deadline -->
                    <div>
                        <p
                            class="text-xs text-slate-400 sm:hidden"
                        >
                            Deadline
                        </p>

                        <p
                            class="text-sm"
                            :class="
                                isOverdue(
                                    assignment
                                )
                                    ? 'text-red-600'
                                    : 'text-slate-500'
                            "
                        >
                            {{
                                formatDate(
                                    assignment.deadline
                                )
                            }}
                        </p>
                    </div>

                    <!-- Status -->
                    <div>
                        <span
                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
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
                    </div>

                    <div
                        class="hidden text-right text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-slate-700 sm:block"
                    >
                        →
                    </div>
                </button>
            </div>
        </div>

        <!-- Drawer -->
        <div
            v-if="selectedAssignment"
            class="fixed inset-0 z-50"
        >
            <button
                type="button"
                aria-label="Close assignment"
                class="absolute inset-0 bg-slate-950/20"
                @click="
                    closeAssignment
                "
            ></button>

            <aside
                class="absolute inset-y-0 right-0 w-full max-w-lg overflow-y-auto border-l border-slate-200 bg-white p-6 shadow-xl"
            >
                <!-- Header -->
                <div
                    class="flex items-start justify-between gap-5"
                >
                    <div
                        class="min-w-0"
                    >
                        <h2
                            class="text-xl font-semibold text-slate-950"
                        >
                            {{
                                selectedAssignment
                                    .title
                            }}
                        </h2>

                        <p
                            class="mt-2 text-sm text-slate-500"
                        >
                            {{
                                selectedAssignment
                                    .student
                                    ?.name
                            }}

                            <span
                                v-if="
                                    selectedAssignment
                                        .subject
                                "
                                class="mx-1.5 text-slate-300"
                            >
                                ·
                            </span>

                            <span
                                v-if="
                                    selectedAssignment
                                        .subject
                                "
                            >
                                {{
                                    selectedAssignment
                                        .subject
                                }}
                            </span>
                        </p>
                    </div>

                    <button
                        type="button"
                        class="shrink-0 text-sm text-slate-500 transition hover:text-slate-950"
                        @click="
                            closeAssignment
                        "
                    >
                        Close
                    </button>
                </div>

                <!-- Details -->
                <dl
                    class="mt-8 divide-y divide-slate-200 border-y border-slate-200"
                >
                    <div
                        class="flex items-center justify-between gap-4 py-4"
                    >
                        <dt
                            class="text-sm text-slate-500"
                        >
                            Status
                        </dt>

                        <dd>
                            <span
                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                :class="
                                    statusClasses(
                                        selectedAssignment
                                            .status
                                    )
                                "
                            >
                                {{
                                    statusLabel(
                                        selectedAssignment
                                            .status
                                    )
                                }}
                            </span>
                        </dd>
                    </div>

                    <div
                        class="flex items-center justify-between gap-4 py-4"
                    >
                        <dt
                            class="text-sm text-slate-500"
                        >
                            Deadline
                        </dt>

                        <dd
                            class="text-right text-sm font-medium text-slate-900"
                        >
                            {{
                                formatDateTime(
                                    selectedAssignment
                                        .deadline
                                )
                            }}
                        </dd>
                    </div>

                    <div
                        v-if="
                            selectedAssignment
                                .grade !== null
                            &&
                            selectedAssignment
                                .grade !== undefined
                        "
                        class="flex items-center justify-between gap-4 py-4"
                    >
                        <dt
                            class="text-sm text-slate-500"
                        >
                            Grade
                        </dt>

                        <dd
                            class="text-sm font-semibold text-slate-900"
                        >
                            {{
                                selectedAssignment
                                    .grade
                            }}
                        </dd>
                    </div>
                </dl>

                <!-- Instructions -->
                <section
                    v-if="
                        selectedAssignment
                            .instructions
                    "
                    class="mt-8"
                >
                    <h3
                        class="text-sm font-semibold text-slate-900"
                    >
                        Instructions
                    </h3>

                    <p
                        class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600"
                    >
                        {{
                            selectedAssignment
                                .instructions
                        }}
                    </p>
                </section>

                <!-- Attachments -->
                <section
                    v-if="
                        selectedAssignment
                            .attachments
                            ?.length
                    "
                    class="mt-8"
                >
                    <h3
                        class="text-sm font-semibold text-slate-900"
                    >
                        Attachments
                    </h3>

                    <div
                        class="mt-3 divide-y divide-slate-200 border-y border-slate-200"
                    >
                        <a
                            v-for="attachment in selectedAssignment.attachments"
                            :key="
                                attachment.id
                            "
                            :href="
                                attachment.url
                            "
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center justify-between gap-4 py-3 text-sm text-slate-700 transition hover:text-slate-950"
                        >
                            <span
                                class="truncate"
                            >
                                {{
                                    attachment.name
                                }}
                            </span>

                            <span
                                class="shrink-0 text-slate-400"
                            >
                                Open
                            </span>
                        </a>
                    </div>
                </section>

                <!-- Feedback -->
                <section
                    v-if="
                        selectedAssignment
                            .feedback
                    "
                    class="mt-8"
                >
                    <h3
                        class="text-sm font-semibold text-slate-900"
                    >
                        Feedback
                    </h3>

                    <p
                        class="mt-3 whitespace-pre-line border-l-2 border-slate-300 pl-4 text-sm leading-6 text-slate-600"
                    >
                        {{
                            selectedAssignment
                                .feedback
                        }}
                    </p>
                </section>

                <!-- Delete -->
                <div
                    class="mt-10 border-t border-slate-200 pt-6"
                >
                    <button
                        type="button"
                        :disabled="
                            deletingAssignmentId ===
                            selectedAssignment.id
                        "
                        class="text-sm font-medium text-red-600 transition hover:text-red-700 disabled:opacity-40"
                        @click="
                            deleteAssignment(
                                selectedAssignment
                            )
                        "
                    >
                        {{
                            deletingAssignmentId ===
                            selectedAssignment.id
                                ? 'Deleting...'
                                : 'Delete assignment'
                        }}
                    </button>
                </div>
            </aside>
        </div>
    </AuthenticatedLayout>
</template>