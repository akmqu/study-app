<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import {
    Head,
    useForm,
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

const selectedAssignment =
    ref(null);

const submissionTarget =
    ref(null);

const showSubmitModal =
    ref(false);

const fileInput =
    ref(null);

const submitForm = useForm({
    answer: '',
    file: null,
});

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
    if (status === 'graded') {
        return 'bg-emerald-50 text-emerald-700';
    }

    if (
        status ===
        'awaiting_review'
    ) {
        return 'bg-amber-50 text-amber-700';
    }

    return 'bg-slate-100 text-slate-700';
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
        return '';
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

const isOverdue = (
    assignment
) => {
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
    if (showSubmitModal.value) {
        return;
    }

    selectedAssignment.value =
        null;
};

const resetSubmitForm = () => {
    submitForm.reset();
    submitForm.clearErrors();

    if (fileInput.value) {
        fileInput.value.value =
            '';
    }
};

const openSubmitModal = (
    assignment
) => {
    submissionTarget.value =
        assignment;

    resetSubmitForm();

    showSubmitModal.value =
        true;
};

const closeSubmitModal = () => {
    if (
        submitForm.processing
    ) {
        return;
    }

    showSubmitModal.value =
        false;

    submissionTarget.value =
        null;

    resetSubmitForm();
};

const handleSubmissionFile = (
    event
) => {
    submitForm.file =
        event.target.files?.[0]
        ?? null;
};

const submitWork = () => {
    if (
        !submissionTarget.value
        || submitForm.processing
    ) {
        return;
    }

    submitForm.post(
        route(
            'student.assignments.submit',
            submissionTarget.value.id
        ),
        {
            preserveScroll: true,
            forceFormData: true,

            onSuccess: () => {
                showSubmitModal.value =
                    false;

                submissionTarget.value =
                    null;

                selectedAssignment.value =
                    null;

                resetSubmitForm();
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
                    Homework from your tutors.
                </p>
            </header>

            <div
                v-if="successMessage"
                class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ successMessage }}
            </div>

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

            <div
                v-else
                class="border-b border-slate-200"
            >
                <button
                    v-for="assignment in filteredAssignments"
                    :key="assignment.id"
                    type="button"
                    class="group grid w-full gap-3 border-b border-slate-200 py-5 text-left transition last:border-b-0 hover:bg-slate-50 sm:grid-cols-[minmax(0,1fr)_150px_150px_32px] sm:items-center sm:px-3"
                    @click="
                        openAssignment(
                            assignment
                        )
                    "
                >
                    <div class="min-w-0">
                        <p
                            class="truncate text-sm font-medium text-slate-900"
                        >
                            {{ assignment.title }}
                        </p>

                        <p
                            class="mt-1 truncate text-sm text-slate-500"
                        >
                            {{
                                assignment.subject
                            }}

                            <span
                                v-if="
                                    assignment.tutor
                                        ?.name
                                "
                                class="mx-1.5 text-slate-300"
                            >
                                ·
                            </span>

                            {{
                                assignment.tutor
                                    ?.name
                            }}
                        </p>
                    </div>

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
                                assignment
                                    .deadline
                            )
                        }}
                    </p>

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

                        <span
                            v-if="
                                assignment.status ===
                                    'graded'
                                &&
                                assignment.grade !==
                                    null
                            "
                            class="ml-2 text-sm font-medium text-slate-700"
                        >
                            {{
                                assignment.grade
                            }}/100
                        </span>
                    </div>

                    <div
                        class="hidden text-right text-slate-300 sm:block"
                    >
                        →
                    </div>
                </button>
            </div>
        </div>

        <!-- Assignment drawer -->
        <div
            v-if="selectedAssignment"
            class="fixed inset-0 z-50"
        >
            <button
                type="button"
                class="absolute inset-0 bg-slate-950/20"
                aria-label="Close"
                @click="
                    closeAssignment
                "
            ></button>

            <aside
                class="absolute inset-y-0 right-0 w-full max-w-lg overflow-y-auto border-l border-slate-200 bg-white p-6 shadow-xl"
            >
                <div
                    class="flex items-start justify-between gap-5"
                >
                    <div>
                        <h2
                            class="text-xl font-semibold text-slate-950"
                        >
                            {{
                                selectedAssignment.title
                            }}
                        </h2>

                        <p
                            class="mt-2 text-sm text-slate-500"
                        >
                            {{
                                selectedAssignment.subject
                            }}

                            <span
                                class="mx-1.5 text-slate-300"
                            >
                                ·
                            </span>

                            {{
                                selectedAssignment
                                    .tutor?.name
                            }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="text-sm text-slate-500 hover:text-slate-950"
                        @click="
                            closeAssignment
                        "
                    >
                        Close
                    </button>
                </div>

                <dl
                    class="mt-8 divide-y divide-slate-200 border-y border-slate-200"
                >
                    <div
                        class="flex justify-between gap-4 py-4"
                    >
                        <dt
                            class="text-sm text-slate-500"
                        >
                            Status
                        </dt>

                        <dd>
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-medium"
                                :class="
                                    statusClasses(
                                        selectedAssignment.status
                                    )
                                "
                            >
                                {{
                                    statusLabel(
                                        selectedAssignment.status
                                    )
                                }}
                            </span>
                        </dd>
                    </div>

                    <div
                        class="flex justify-between gap-4 py-4"
                    >
                        <dt
                            class="text-sm text-slate-500"
                        >
                            Deadline
                        </dt>

                        <dd
                            class="text-sm font-medium text-slate-900"
                        >
                            {{
                                selectedAssignment.deadline
                                    ? formatDateTime(
                                        selectedAssignment.deadline
                                    )
                                    : 'No deadline'
                            }}
                        </dd>
                    </div>

                    <div
                        v-if="
                            selectedAssignment.status ===
                                'graded'
                            &&
                            selectedAssignment.grade !==
                                null
                        "
                        class="flex justify-between gap-4 py-4"
                    >
                        <dt
                            class="text-sm text-slate-500"
                        >
                            Grade
                        </dt>

                        <dd
                            class="text-sm font-semibold"
                        >
                            {{
                                selectedAssignment.grade
                            }}/100
                        </dd>
                    </div>
                </dl>

                <section
                    v-if="
                        selectedAssignment.instructions
                    "
                    class="mt-8"
                >
                    <h3
                        class="text-sm font-semibold"
                    >
                        Instructions
                    </h3>

                    <p
                        class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600"
                    >
                        {{
                            selectedAssignment.instructions
                        }}
                    </p>
                </section>

                <section
                    v-if="
                        selectedAssignment.attachments
                            ?.length
                    "
                    class="mt-8"
                >
                    <h3
                        class="text-sm font-semibold"
                    >
                        Tutor attachments
                    </h3>

                    <div
                        class="mt-3 divide-y divide-slate-200 border-y"
                    >
                        <a
                            v-for="attachment in selectedAssignment.attachments"
                            :key="attachment.id"
                            :href="attachment.url"
                            target="_blank"
                            class="flex justify-between py-3 text-sm"
                        >
                            {{ attachment.name }}

                            <span
                                class="text-slate-400"
                            >
                                Open
                            </span>
                        </a>
                    </div>
                </section>

                <section
                    v-if="
                        selectedAssignment.submission
                    "
                    class="mt-8"
                >
                    <h3
                        class="text-sm font-semibold"
                    >
                        Your submission
                    </h3>

                    <p
                        class="mt-2 text-sm text-slate-500"
                    >
                        Submitted
                        {{
                            formatDateTime(
                                selectedAssignment
                                    .submission
                                    .submitted_at
                            )
                        }}
                    </p>

                    <div
                        v-if="
                            selectedAssignment
                                .submission
                                .answer
                        "
                        class="mt-4"
                    >
                        <p
                            class="text-xs font-medium uppercase tracking-wide text-slate-400"
                        >
                            Your answer
                        </p>

                        <p
                            class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700"
                        >
                            {{
                                selectedAssignment
                                    .submission
                                    .answer
                            }}
                        </p>
                    </div>

                    <a
                        v-if="
                            selectedAssignment
                                .submission
                                .file_url
                        "
                        :href="
                            selectedAssignment
                                .submission
                                .file_url
                        "
                        target="_blank"
                        class="mt-4 inline-block text-sm font-medium underline underline-offset-4"
                    >
                        Open submitted file
                    </a>
                </section>

                <section
                    v-if="
                        selectedAssignment.feedback
                    "
                    class="mt-8"
                >
                    <h3
                        class="text-sm font-semibold"
                    >
                        Tutor feedback
                    </h3>

                    <p
                        class="mt-3 whitespace-pre-line border-l-2 border-slate-300 pl-4 text-sm leading-6 text-slate-600"
                    >
                        {{
                            selectedAssignment.feedback
                        }}
                    </p>
                </section>

                <div
                    class="mt-10 border-t border-slate-200 pt-6"
                >
                    <button
                        v-if="
                            selectedAssignment.status ===
                                'todo'
                        "
                        type="button"
                        class="w-full rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white"
                        @click="
                            openSubmitModal(
                                selectedAssignment
                            )
                        "
                    >
                        Submit work
                    </button>

                    <p
                        v-else-if="
                            selectedAssignment.status ===
                                'awaiting_review'
                        "
                        class="text-sm text-slate-500"
                    >
                        Your work is waiting
                        for tutor review.
                    </p>

                    <button
                        v-else
                        type="button"
                        class="w-full rounded-md border border-slate-300 px-4 py-2.5 text-sm font-medium"
                        @click="
                            openSubmitModal(
                                selectedAssignment
                            )
                        "
                    >
                        Resubmit work
                    </button>
                </div>
            </aside>
        </div>

        <!-- Submission modal -->
        <div
            v-if="showSubmitModal"
            class="fixed inset-0 z-[60]"
        >
            <button
                type="button"
                class="absolute inset-0 bg-slate-950/30"
                aria-label="Close"
                @click="
                    closeSubmitModal
                "
            ></button>

            <div
                class="absolute inset-x-4 top-16 mx-auto max-h-[calc(100vh-8rem)] max-w-lg overflow-y-auto rounded-lg bg-white shadow-xl"
            >
                <div
                    class="border-b border-slate-200 px-6 py-5"
                >
                    <h2
                        class="text-lg font-semibold"
                    >
                        {{
                            submissionTarget
                                ?.status ===
                                'graded'
                                ? 'Resubmit work'
                                : 'Submit work'
                        }}
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        {{
                            submissionTarget
                                ?.title
                        }}
                    </p>
                </div>

                <form
                    class="p-6"
                    @submit.prevent="
                        submitWork
                    "
                >
                    <div>
                        <label
                            for="submission-answer"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Your answer
                        </label>

                        <textarea
                            id="submission-answer"
                            v-model="
                                submitForm.answer
                            "
                            rows="6"
                            placeholder="Write your answer here..."
                            class="mt-2 block w-full resize-y rounded-md border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:ring-slate-500"
                        ></textarea>

                        <p
                            v-if="
                                submitForm.errors
                                    .answer
                            "
                            class="mt-2 text-xs text-red-600"
                        >
                            {{
                                submitForm.errors
                                    .answer
                            }}
                        </p>
                    </div>

                    <div
                        class="my-5 flex items-center gap-3"
                    >
                        <div
                            class="h-px flex-1 bg-slate-200"
                        ></div>

                        <span
                            class="text-xs uppercase tracking-wide text-slate-400"
                        >
                            or attach a file
                        </span>

                        <div
                            class="h-px flex-1 bg-slate-200"
                        ></div>
                    </div>

                    <div>
                        <label
                            for="submission-file"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Completed work
                        </label>

                        <input
                            id="submission-file"
                            ref="fileInput"
                            type="file"
                            accept=".pdf,.doc,.docx"
                            class="mt-2 block w-full text-sm text-slate-500 file:mr-3 file:rounded-md file:border file:border-slate-300 file:bg-white file:px-3 file:py-2 file:text-sm"
                            @change="
                                handleSubmissionFile
                            "
                        />

                        <p
                            class="mt-2 text-xs text-slate-400"
                        >
                            Optional. PDF or Word,
                            maximum 10 MB.
                        </p>

                        <p
                            v-if="
                                submitForm.errors.file
                            "
                            class="mt-2 text-xs text-red-600"
                        >
                            {{
                                submitForm.errors.file
                            }}
                        </p>

                        <p
                            v-if="
                                submitForm.errors
                                    .submission
                            "
                            class="mt-2 text-xs text-red-600"
                        >
                            {{
                                submitForm.errors
                                    .submission
                            }}
                        </p>
                    </div>

                    <div
                        class="mt-6 flex justify-end gap-3"
                    >
                        <button
                            type="button"
                            :disabled="
                                submitForm.processing
                            "
                            class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium"
                            @click="
                                closeSubmitModal
                            "
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            :disabled="
                                submitForm.processing
                                ||
                                (
                                    !submitForm.file
                                    &&
                                    !String(
                                        submitForm.answer
                                        ?? ''
                                    ).trim()
                                )
                            "
                            class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-40"
                        >
                            {{
                                submitForm.processing
                                    ? 'Submitting...'
                                    : 'Submit'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>