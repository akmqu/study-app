<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

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

const successMessage = computed(() => page.props.flash?.success ?? null);

const activeFilter = ref('all');
const selectedAssignment = ref(null);
const showCreateModal = ref(false);
const deletingAssignmentId = ref(null);
const attachmentInput = ref(null);

const aiPrompt = ref('');
const aiGenerating = ref(false);
const aiError = ref('');

const filters = [
    { key: 'all', label: 'All' },
    { key: 'todo', label: 'To do' },
    { key: 'awaiting_review', label: 'Awaiting review' },
    { key: 'graded', label: 'Graded' },
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

const selectedStudent = computed(() => {
    return props.students.find(
        (student) => Number(student.id) === Number(createForm.student_id)
    );
});

const availableSubjects = computed(() => {
    return selectedStudent.value?.subjects ?? [];
});

watch(
    () => createForm.student_id,
    () => {
        createForm.subject = '';
    }
);

const filteredAssignments = computed(() => {
    if (activeFilter.value === 'all') {
        return props.assignments;
    }

    return props.assignments.filter(
        (assignment) => assignment.status === activeFilter.value
    );
});

const filterCount = (status) => {
    if (status === 'all') {
        return props.assignments.length;
    }

    return props.assignments.filter(
        (assignment) => assignment.status === status
    ).length;
};

const statusLabel = (status) => {
    if (status === 'awaiting_review') return 'Awaiting review';
    if (status === 'graded') return 'Graded';

    return 'To do';
};

const statusClasses = (status) => {
    if (status === 'graded') {
        return 'bg-emerald-50 text-emerald-700';
    }

    if (status === 'awaiting_review') {
        return 'bg-amber-50 text-amber-700';
    }

    return 'bg-slate-100 text-slate-700';
};

const rowClasses = (assignment) => {
    if (assignment.status === 'awaiting_review') {
        return 'hover:bg-amber-50/40';
    }

    return 'hover:bg-slate-50';
};

const formatDate = (value) => {
    if (!value) return 'No deadline';

    return new Intl.DateTimeFormat(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(new Date(value));
};

const formatDateTime = (value) => {
    if (!value) return '';

    return new Intl.DateTimeFormat(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
};

const isOverdue = (assignment) => {
    if (assignment.status !== 'todo' || !assignment.deadline) {
        return false;
    }

    return new Date(assignment.deadline) < new Date();
};

const resetCreateForm = () => {
    createForm.reset();
    createForm.clearErrors();

    aiPrompt.value = '';
    aiError.value = '';

    if (attachmentInput.value) {
        attachmentInput.value.value = '';
    }
};

const openCreateModal = () => {
    resetCreateForm();
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    if (createForm.processing || aiGenerating.value) {
        return;
    }

    showCreateModal.value = false;
    resetCreateForm();
};

const handleAttachments = (event) => {
    createForm.attachments = Array.from(event.target.files ?? []);
};

const generateWithAi = async () => {
    const subject = String(createForm.subject ?? '').trim();
    const prompt = String(aiPrompt.value ?? '').trim();

    if (!subject) {
        aiError.value = 'Choose a subject first.';
        return;
    }

    if (!prompt) {
        aiError.value = 'Enter a prompt for AI.';
        return;
    }

    if (aiGenerating.value) {
        return;
    }

    aiGenerating.value = true;
    aiError.value = '';

    try {
        const response = await window.axios.post(
            route('tutor.assignments.generate-ai'),
            {
                subject,
                prompt,
            }
        );

        createForm.instructions = response.data.instructions ?? '';
    } catch (error) {
        aiError.value =
            error.response?.data?.message
            ?? 'AI generation failed. Please try again.';
    } finally {
        aiGenerating.value = false;
    }
};

const submitAssignment = () => {
    if (createForm.processing || aiGenerating.value) {
        return;
    }

    createForm.post(route('tutor.assignments.store'), {
        preserveScroll: true,
        forceFormData: true,

        onSuccess: () => {
            showCreateModal.value = false;
            resetCreateForm();
        },
    });
};

const openAssignment = (assignment) => {
    selectedAssignment.value = assignment;

    reviewForm.clearErrors();
    reviewForm.grade = assignment.grade ?? '';
    reviewForm.feedback = assignment.feedback ?? '';
};

const closeAssignment = () => {
    if (deletingAssignmentId.value || reviewForm.processing) {
        return;
    }

    selectedAssignment.value = null;
    reviewForm.reset();
    reviewForm.clearErrors();
};

const saveReview = () => {
    const submission = selectedAssignment.value?.submission;

    if (!submission?.id || reviewForm.processing) {
        return;
    }

    reviewForm.patch(
        route('tutor.submissions.grade', submission.id),
        {
            preserveScroll: true,

            onSuccess: () => {
                selectedAssignment.value = null;
                reviewForm.reset();
                reviewForm.clearErrors();
            },
        }
    );
};

const deleteAssignment = (assignment) => {
    if (deletingAssignmentId.value) {
        return;
    }

    const confirmed = window.confirm(
        `Delete "${assignment.title}"?`
    );

    if (!confirmed) {
        return;
    }

    deletingAssignmentId.value = assignment.id;

    router.delete(
        route('tutor.assignments.destroy', assignment.id),
        {
            preserveScroll: true,

            onSuccess: () => {
                selectedAssignment.value = null;
            },

            onFinish: () => {
                deletingAssignmentId.value = null;
            },
        }
    );
};
</script>

<template>
    <Head title="Assignments" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-6xl">
            <header
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-950"
                    >
                        Assignments
                    </h1>

                    <p class="mt-1.5 text-sm text-slate-500">
                        Create, review and manage student homework.
                    </p>
                </div>

                <button
                    type="button"
                    :disabled="students.length === 0"
                    class="rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-40"
                    @click="openCreateModal"
                >
                    + New assignment
                </button>
            </header>

            <div
                v-if="successMessage"
                class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ successMessage }}
            </div>

            <section
                class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <div class="border-b border-slate-200 px-5">
                    <div class="flex flex-wrap gap-1">
                        <button
                            v-for="filter in filters"
                            :key="filter.key"
                            type="button"
                            class="-mb-px border-b-2 px-3 py-4 text-sm font-medium transition"
                            :class="
                                activeFilter === filter.key
                                    ? 'border-slate-900 text-slate-950'
                                    : 'border-transparent text-slate-500 hover:text-slate-900'
                            "
                            @click="activeFilter = filter.key"
                        >
                            {{ filter.label }}

                            <span class="ml-1.5 text-xs text-slate-400">
                                {{ filterCount(filter.key) }}
                            </span>
                        </button>
                    </div>
                </div>

                <div
                    v-if="filteredAssignments.length === 0"
                    class="px-5 py-14"
                >
                    <p class="text-sm font-medium text-slate-900">
                        No assignments here
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        There are no assignments in this category.
                    </p>
                </div>

                <div v-else>
                    <button
                        v-for="assignment in filteredAssignments"
                        :key="assignment.id"
                        type="button"
                        class="group grid w-full gap-3 border-b border-slate-100 px-5 py-5 text-left transition last:border-b-0 sm:grid-cols-[minmax(0,1fr)_160px_170px_24px] sm:items-center"
                        :class="rowClasses(assignment)"
                        @click="openAssignment(assignment)"
                    >
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p
                                    class="truncate text-sm font-medium text-slate-900"
                                >
                                    {{ assignment.title }}
                                </p>

                                <span
                                    v-if="isOverdue(assignment)"
                                    class="text-xs font-medium text-red-600"
                                >
                                    Overdue
                                </span>
                            </div>

                            <p class="mt-1 truncate text-sm text-slate-500">
                                {{ assignment.student?.name }}

                                <span
                                    v-if="assignment.subject"
                                    class="mx-1.5 text-slate-300"
                                >
                                    ·
                                </span>

                                {{ assignment.subject }}
                            </p>
                        </div>

                        <p
                            class="text-sm"
                            :class="
                                isOverdue(assignment)
                                    ? 'text-red-600'
                                    : 'text-slate-500'
                            "
                        >
                            {{ formatDate(assignment.deadline) }}
                        </p>

                        <div>
                            <span
                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                :class="statusClasses(assignment.status)"
                            >
                                {{ statusLabel(assignment.status) }}
                            </span>
                        </div>

                        <div
                            class="hidden text-right text-slate-300 transition group-hover:text-slate-700 sm:block"
                        >
                            →
                        </div>
                    </button>
                </div>
            </section>
        </div>

        <!-- Create modal -->
        <div
            v-if="showCreateModal"
            class="fixed inset-0 z-50"
        >
            <button
                type="button"
                class="absolute inset-0 bg-slate-950/25"
                aria-label="Close"
                @click="closeCreateModal"
            ></button>

            <div
                class="absolute inset-x-4 top-8 mx-auto max-h-[calc(100vh-4rem)] max-w-xl overflow-y-auto rounded-xl bg-white shadow-xl sm:top-16"
            >
                <div
                    class="flex items-start justify-between border-b border-slate-200 px-6 py-5"
                >
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950">
                            New assignment
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Create homework for a student.
                        </p>
                    </div>

                    <button
                        type="button"
                        :disabled="aiGenerating"
                        class="text-sm text-slate-500 hover:text-slate-950 disabled:opacity-40"
                        @click="closeCreateModal"
                    >
                        Close
                    </button>
                </div>

                <form
                    class="space-y-5 p-6"
                    @submit.prevent="submitAssignment"
                >
                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700"
                        >
                            Student
                        </label>

                        <select
                            v-model="createForm.student_id"
                            required
                            class="mt-2 block w-full rounded-md border-slate-300"
                        >
                            <option value="" disabled>
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

                        <p
                            v-if="createForm.errors.student_id"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ createForm.errors.student_id }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700"
                        >
                            Subject
                        </label>

                        <select
                            v-model="createForm.subject"
                            required
                            :disabled="!createForm.student_id"
                            class="mt-2 block w-full rounded-md border-slate-300 disabled:bg-slate-100"
                        >
                            <option value="" disabled>
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

                        <p
                            v-if="createForm.errors.subject"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ createForm.errors.subject }}
                        </p>
                    </div>

                    <!-- Normal assignment title -->
                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700"
                        >
                            Title
                        </label>

                        <input
                            v-model="createForm.title"
                            type="text"
                            required
                            placeholder="e.g. Homework #3"
                            class="mt-2 block w-full rounded-md border-slate-300"
                        />

                        <p
                            v-if="createForm.errors.title"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ createForm.errors.title }}
                        </p>
                    </div>

                    <!-- AI prompt -->
                    <div
                        class="rounded-lg border border-indigo-100 bg-indigo-50/50 p-4"
                    >
                        <div>
                            <p
                                class="text-sm font-medium text-slate-900"
                            >
                                ✨ AI Assistant
                            </p>

                            <p
                                class="mt-1 text-xs leading-5 text-slate-500"
                            >
                                Describe what homework you want Gemini to generate.
                            </p>
                        </div>

                        <textarea
                            v-model="aiPrompt"
                            rows="3"
                            placeholder="e.g. Create 6 exercises about adding and subtracting fractions for a 12-year-old. Make the last two exercises harder."
                            class="mt-3 block w-full resize-y rounded-md border-slate-300 bg-white"
                        ></textarea>

                        <p
                            v-if="aiError"
                            class="mt-2 text-xs text-red-600"
                        >
                            {{ aiError }}
                        </p>

                        <button
                            type="button"
                            :disabled="
                                aiGenerating
                                || !createForm.subject
                                || !aiPrompt.trim()
                            "
                            class="mt-3 inline-flex items-center justify-center rounded-md border border-indigo-200 bg-white px-3 py-2 text-sm font-medium text-indigo-700 transition hover:bg-indigo-50 disabled:cursor-not-allowed disabled:opacity-40"
                            @click="generateWithAi"
                        >
                            {{
                                aiGenerating
                                    ? 'Generating...'
                                    : '✨ Generate instructions'
                            }}
                        </button>
                    </div>

                    <!-- Instructions -->
                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700"
                        >
                            Instructions
                        </label>

                        <textarea
                            v-model="createForm.instructions"
                            rows="7"
                            placeholder="Write instructions yourself or generate them with AI..."
                            class="mt-2 block w-full resize-y rounded-md border-slate-300"
                        ></textarea>

                        <p class="mt-2 text-xs text-slate-400">
                            You can edit the generated instructions before creating the assignment.
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700"
                        >
                            Deadline
                        </label>

                        <input
                            v-model="createForm.deadline"
                            type="date"
                            class="mt-2 block w-full rounded-md border-slate-300"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700"
                        >
                            Attachments
                        </label>

                        <input
                            ref="attachmentInput"
                            type="file"
                            multiple
                            accept=".pdf,.doc,.docx"
                            class="mt-2 block w-full text-sm text-slate-500"
                            @change="handleAttachments"
                        />

                        <p class="mt-2 text-xs text-slate-400">
                            PDF or Word. Maximum 10 MB per file.
                        </p>
                    </div>

                    <div
                        class="flex justify-end gap-3 border-t border-slate-200 pt-5"
                    >
                        <button
                            type="button"
                            :disabled="aiGenerating"
                            class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                            @click="closeCreateModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            :disabled="
                                createForm.processing
                                || aiGenerating
                            "
                            class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-40"
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

        <!-- Assignment drawer -->
        <div
            v-if="selectedAssignment"
            class="fixed inset-0 z-50"
        >
            <button
                type="button"
                class="absolute inset-0 bg-slate-950/20"
                aria-label="Close"
                @click="closeAssignment"
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
                            {{ selectedAssignment.title }}
                        </h2>

                        <p class="mt-2 text-sm text-slate-500">
                            {{ selectedAssignment.student?.name }}

                            <span class="mx-1 text-slate-300">
                                ·
                            </span>

                            {{ selectedAssignment.subject }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="text-sm text-slate-500 hover:text-slate-950"
                        @click="closeAssignment"
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
                        <dt class="text-sm text-slate-500">
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
                        <dt class="text-sm text-slate-500">
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
                </dl>

                <section
                    v-if="selectedAssignment.instructions"
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
                        {{ selectedAssignment.instructions }}
                    </p>
                </section>

                <section
                    v-if="selectedAssignment.submission"
                    class="mt-8"
                >
                    <h3
                        class="text-sm font-semibold text-slate-900"
                    >
                        Student submission
                    </h3>

                    <p class="mt-2 text-sm text-slate-500">
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
                        class="mt-4 rounded-lg bg-slate-50 p-4"
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
                        rel="noopener noreferrer"
                        class="mt-4 inline-block text-sm font-medium text-slate-700 underline underline-offset-4 hover:text-slate-950"
                    >
                        Open submitted file
                    </a>
                </section>

                <section
                    v-if="selectedAssignment.submission"
                    class="mt-8 rounded-xl border border-slate-200 bg-slate-50 p-5"
                >
                    <h3
                        class="text-sm font-semibold text-slate-900"
                    >
                        Review submission
                    </h3>

                    <div class="mt-4">
                        <label
                            class="block text-sm font-medium text-slate-700"
                        >
                            Grade
                        </label>

                        <div class="mt-2 flex items-center gap-2">
                            <input
                                v-model="reviewForm.grade"
                                type="number"
                                min="0"
                                max="100"
                                required
                                class="w-28 rounded-md border-slate-300"
                            />

                            <span class="text-sm text-slate-500">
                                / 100
                            </span>
                        </div>

                        <p
                            v-if="reviewForm.errors.grade"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ reviewForm.errors.grade }}
                        </p>
                    </div>

                    <div class="mt-4">
                        <label
                            class="block text-sm font-medium text-slate-700"
                        >
                            Feedback
                        </label>

                        <textarea
                            v-model="reviewForm.feedback"
                            rows="5"
                            placeholder="Add feedback for the student..."
                            class="mt-2 block w-full resize-y rounded-md border-slate-300"
                        ></textarea>

                        <p
                            v-if="reviewForm.errors.feedback"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ reviewForm.errors.feedback }}
                        </p>
                    </div>

                    <button
                        type="button"
                        :disabled="reviewForm.processing"
                        class="mt-4 w-full rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-40"
                        @click="saveReview"
                    >
                        {{
                            reviewForm.processing
                                ? 'Saving...'
                                : selectedAssignment.status === 'graded'
                                    ? 'Update grade'
                                    : 'Save grade'
                        }}
                    </button>
                </section>

                <div
                    v-else
                    class="mt-8 rounded-lg bg-slate-50 p-4"
                >
                    <p class="text-sm text-slate-500">
                        The student has not submitted work yet.
                    </p>
                </div>

                <div
                    class="mt-10 border-t border-slate-200 pt-6"
                >
                    <button
                        type="button"
                        :disabled="
                            deletingAssignmentId
                            === selectedAssignment.id
                        "
                        class="text-sm font-medium text-red-600 hover:text-red-700 disabled:opacity-40"
                        @click="
                            deleteAssignment(
                                selectedAssignment
                            )
                        "
                    >
                        {{
                            deletingAssignmentId
                            === selectedAssignment.id
                                ? 'Deleting...'
                                : 'Delete assignment'
                        }}
                    </button>
                </div>
            </aside>
        </div>
    </AuthenticatedLayout>
</template>