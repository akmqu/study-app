<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import {
    Head,
    router,
    useForm,
    usePage,
} from '@inertiajs/vue3';

import {
    computed,
    ref,
    watch,
} from 'vue';

const props = defineProps({
    assignments: {
        type: Array,
        default: () => [],
    },

    students: {
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

const showCreateModal =
    ref(false);

const deletingAssignmentId =
    ref(null);

const attachmentInput =
    ref(null);

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

const createForm = useForm({
    student_id: '',
    subject: '',
    title: '',
    instructions: '',
    deadline: '',
    attachments: [],
});

const reviewForm = useForm({
    grade: '',
    feedback: '',
});

const selectedStudent =
    computed(() => {
        return props.students.find(
            (student) =>
                Number(student.id) ===
                Number(
                    createForm
                        .student_id
                )
        );
    });

const availableSubjects =
    computed(() => {
        return (
            selectedStudent.value
                ?.subjects
            ?? []
        );
    });

watch(
    () =>
        createForm.student_id,
    () => {
        createForm.subject = '';
    }
);

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

const resetCreateForm = () => {
    createForm.reset();
    createForm.clearErrors();

    if (
        attachmentInput.value
    ) {
        attachmentInput.value.value =
            '';
    }
};

const openCreateModal = () => {
    resetCreateForm();

    showCreateModal.value =
        true;
};

const closeCreateModal = () => {
    if (
        createForm.processing
    ) {
        return;
    }

    showCreateModal.value =
        false;

    resetCreateForm();
};

const handleAttachments = (
    event
) => {
    createForm.attachments =
        Array.from(
            event.target.files ?? []
        );
};

const submitAssignment = () => {
    if (
        createForm.processing
    ) {
        return;
    }

    createForm.post(
        route(
            'tutor.assignments.store'
        ),
        {
            preserveScroll: true,
            forceFormData: true,

            onSuccess: () => {
                showCreateModal.value =
                    false;

                resetCreateForm();
            },
        }
    );
};

const openAssignment = (
    assignment
) => {
    selectedAssignment.value =
        assignment;

    reviewForm.clearErrors();

    reviewForm.grade =
        assignment.grade
        ?? '';

    reviewForm.feedback =
        assignment.feedback
        ?? '';
};

const closeAssignment = () => {
    if (
        deletingAssignmentId.value
        || reviewForm.processing
    ) {
        return;
    }

    selectedAssignment.value =
        null;

    reviewForm.reset();
    reviewForm.clearErrors();
};

const saveReview = () => {
    const submission =
        selectedAssignment.value
            ?.submission;

    if (
        !submission?.id
        || reviewForm.processing
    ) {
        return;
    }

    reviewForm.patch(
        route(
            'tutor.submissions.grade',
            submission.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                selectedAssignment.value =
                    null;

                reviewForm.reset();
                reviewForm.clearErrors();
            },
        }
    );
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
            <header
                class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-950"
                    >
                        Assignments
                    </h1>

                    <p
                        class="mt-2 text-sm text-slate-500"
                    >
                        Create, review and manage
                        student homework.
                    </p>
                </div>

                <button
                    type="button"
                    :disabled="
                        students.length === 0
                    "
                    class="rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white disabled:opacity-40"
                    @click="
                        openCreateModal
                    "
                >
                    + New assignment
                </button>
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
                    class="-mb-px border-b-2 px-3 py-3 text-sm font-medium"
                    :class="
                        activeFilter ===
                        filter.key
                            ? 'border-slate-900 text-slate-950'
                            : 'border-transparent text-slate-500'
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
                    class="text-sm font-medium"
                >
                    No assignments here
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
                    class="grid w-full gap-3 border-b border-slate-200 py-5 text-left sm:grid-cols-[minmax(0,1fr)_160px_150px_32px] sm:items-center sm:px-3"
                    @click="
                        openAssignment(
                            assignment
                        )
                    "
                >
                    <div>
                        <p
                            class="text-sm font-medium"
                        >
                            {{
                                assignment.title
                            }}
                        </p>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            {{
                                assignment.student
                                    ?.name
                            }}
                            ·
                            {{
                                assignment.subject
                            }}
                        </p>
                    </div>

                    <p
                        class="text-sm text-slate-500"
                    >
                        {{
                            formatDate(
                                assignment.deadline
                            )
                        }}
                    </p>

                    <span
                        class="w-fit rounded-full px-2.5 py-1 text-xs font-medium"
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
                        class="hidden text-right text-slate-400 sm:block"
                    >
                        →
                    </span>
                </button>
            </div>
        </div>

        <!-- Create -->
        <div
            v-if="showCreateModal"
            class="fixed inset-0 z-50"
        >
            <button
                class="absolute inset-0 bg-slate-950/25"
                @click="
                    closeCreateModal
                "
            ></button>

            <div
                class="absolute inset-x-4 top-8 mx-auto max-h-[calc(100vh-4rem)] max-w-xl overflow-y-auto rounded-lg bg-white shadow-xl"
            >
                <div
                    class="flex justify-between border-b px-6 py-5"
                >
                    <div>
                        <h2
                            class="text-lg font-semibold"
                        >
                            New assignment
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Create homework for a student.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="
                            closeCreateModal
                        "
                    >
                        Close
                    </button>
                </div>

                <form
                    class="space-y-5 p-6"
                    @submit.prevent="
                        submitAssignment
                    "
                >
                    <div>
                        <label
                            class="block text-sm font-medium"
                        >
                            Student
                        </label>

                        <select
                            v-model="
                                createForm.student_id
                            "
                            class="mt-2 block w-full rounded-md border-slate-300"
                            required
                        >
                            <option
                                value=""
                                disabled
                            >
                                Select student
                            </option>

                            <option
                                v-for="student in students"
                                :key="student.id"
                                :value="student.id"
                            >
                                {{ student.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium"
                        >
                            Subject
                        </label>

                        <select
                            v-model="
                                createForm.subject
                            "
                            class="mt-2 block w-full rounded-md border-slate-300"
                            :disabled="
                                !createForm.student_id
                            "
                            required
                        >
                            <option
                                value=""
                                disabled
                            >
                                Select subject
                            </option>

                            <option
                                v-for="subject in availableSubjects"
                                :key="subject"
                                :value="subject"
                            >
                                {{ subject }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium"
                        >
                            Title
                        </label>

                        <input
                            v-model="
                                createForm.title
                            "
                            type="text"
                            required
                            class="mt-2 block w-full rounded-md border-slate-300"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium"
                        >
                            Instructions
                        </label>

                        <textarea
                            v-model="
                                createForm.instructions
                            "
                            rows="5"
                            class="mt-2 block w-full rounded-md border-slate-300"
                        ></textarea>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium"
                        >
                            Deadline
                        </label>

                        <input
                            v-model="
                                createForm.deadline
                            "
                            type="date"
                            class="mt-2 block w-full rounded-md border-slate-300"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium"
                        >
                            Attachments
                        </label>

                        <input
                            ref="attachmentInput"
                            type="file"
                            multiple
                            accept=".pdf,.doc,.docx"
                            class="mt-2 block w-full"
                            @change="
                                handleAttachments
                            "
                        />
                    </div>

                    <div
                        class="flex justify-end gap-3 border-t pt-5"
                    >
                        <button
                            type="button"
                            class="rounded-md border px-4 py-2 text-sm"
                            @click="
                                closeCreateModal
                            "
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="rounded-md bg-slate-900 px-4 py-2 text-sm text-white"
                        >
                            {{
                                createForm.processing
                                    ? 'Creating...'
                                    : 'Create assignment'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Drawer -->
        <div
            v-if="selectedAssignment"
            class="fixed inset-0 z-50"
        >
            <button
                class="absolute inset-0 bg-slate-950/20"
                @click="
                    closeAssignment
                "
            ></button>

            <aside
                class="absolute inset-y-0 right-0 w-full max-w-lg overflow-y-auto border-l bg-white p-6 shadow-xl"
            >
                <div
                    class="flex justify-between gap-5"
                >
                    <div>
                        <h2
                            class="text-xl font-semibold"
                        >
                            {{
                                selectedAssignment.title
                            }}
                        </h2>

                        <p
                            class="mt-2 text-sm text-slate-500"
                        >
                            {{
                                selectedAssignment
                                    .student?.name
                            }}
                            ·
                            {{
                                selectedAssignment.subject
                            }}
                        </p>
                    </div>

                    <button
                        @click="
                            closeAssignment
                        "
                    >
                        Close
                    </button>
                </div>

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
                        selectedAssignment.submission
                    "
                    class="mt-8"
                >
                    <h3
                        class="text-sm font-semibold"
                    >
                        Student submission
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
                                .submission.answer
                        "
                        class="mt-5"
                    >
                        <p
                            class="text-xs font-medium uppercase tracking-wide text-slate-400"
                        >
                            Student answer
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
                        class="mt-5 inline-block text-sm font-medium underline underline-offset-4"
                    >
                        Open submitted file
                    </a>
                </section>

                <section
                    v-if="
                        selectedAssignment.submission
                    "
                    class="mt-8 border-t pt-8"
                >
                    <h3
                        class="text-sm font-semibold"
                    >
                        Review
                    </h3>

                    <div
                        class="mt-4"
                    >
                        <label
                            class="block text-sm font-medium"
                        >
                            Grade
                        </label>

                        <div
                            class="mt-2 flex items-center gap-2"
                        >
                            <input
                                v-model="
                                    reviewForm.grade
                                "
                                type="number"
                                min="0"
                                max="100"
                                required
                                class="w-28 rounded-md border-slate-300"
                            />

                            <span
                                class="text-sm text-slate-500"
                            >
                                / 100
                            </span>
                        </div>
                    </div>

                    <div
                        class="mt-4"
                    >
                        <label
                            class="block text-sm font-medium"
                        >
                            Feedback
                        </label>

                        <textarea
                            v-model="
                                reviewForm.feedback
                            "
                            rows="5"
                            class="mt-2 block w-full rounded-md border-slate-300"
                        ></textarea>
                    </div>

                    <button
                        type="button"
                        class="mt-4 w-full rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white"
                        @click="
                            saveReview
                        "
                    >
                        {{
                            reviewForm.processing
                                ? 'Saving...'
                                : 'Save grade'
                        }}
                    </button>
                </section>

                <p
                    v-else
                    class="mt-8 border-t pt-8 text-sm text-slate-500"
                >
                    The student has not submitted work yet.
                </p>

                <div
                    class="mt-10 border-t pt-6"
                >
                    <button
                        type="button"
                        class="text-sm font-medium text-red-600"
                        @click="
                            deleteAssignment(
                                selectedAssignment
                            )
                        "
                    >
                        Delete assignment
                    </button>
                </div>
            </aside>
        </div>
    </AuthenticatedLayout>
</template>