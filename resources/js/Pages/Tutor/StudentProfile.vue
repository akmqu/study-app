<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    Head,
    Link,
    useForm,
    usePage,
} from '@inertiajs/vue3';

import {
    computed,
    ref,
} from 'vue';

const props = defineProps({
    student: {
        type: Object,
        required: true,
    },

    privateNotes: {
        type: String,
        default: '',
    },

    subjects: {
        type: Array,
        default: () => [],
    },

    assignments: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const successMessage = computed(
    () => page.props.flash?.success ?? null
);

const showHomeworkForm = ref(false);

const notesForm = useForm({
    private_notes:
        props.privateNotes,
});

const homeworkForm = useForm({
    student_id:
        props.student.id,

    subject:
        props.subjects.length === 1
            ? props.subjects[0]
            : '',

    title: '',
    instructions: '',
    deadline: '',
    attachments: [],
});

const initials = computed(() => {
    return String(
        props.student.name ?? ''
    )
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map(
            (part) =>
                part
                    .charAt(0)
                    .toUpperCase()
        )
        .join('');
});

/*
|--------------------------------------------------------------------------
| Assignment stats
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
            assignment.status
            === 'awaiting_review'
    ).length;
});

const gradedCount = computed(() => {
    return props.assignments.filter(
        (assignment) =>
            assignment.status === 'graded'
    ).length;
});

const averageGrade = computed(() => {
    const grades =
        props.assignments
            .filter(
                (assignment) =>
                    assignment.status
                        === 'graded'
                    && assignment.grade
                        !== null
                    && assignment.grade
                        !== undefined
            )
            .map(
                (assignment) =>
                    Number(
                        assignment.grade
                    )
            )
            .filter(
                (grade) =>
                    Number.isFinite(grade)
            );

    if (!grades.length) {
        return 0;
    }

    return Math.round(
        grades.reduce(
            (sum, grade) =>
                sum + grade,
            0
        ) / grades.length
    );
});

/*
|--------------------------------------------------------------------------
| Helpers
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
        }
    ).format(
        new Date(value)
    );
};

const statusLabel = (status) => {
    if (
        status ===
        'awaiting_review'
    ) {
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

    if (
        status ===
        'awaiting_review'
    ) {
        return 'bg-amber-50 text-amber-700';
    }

    return 'bg-indigo-50 text-indigo-700';
};

const isOverdue = (assignment) => {
    if (
        assignment.status !== 'todo'
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

/*
|--------------------------------------------------------------------------
| Private notes
|--------------------------------------------------------------------------
*/

const saveNotes = () => {
    if (notesForm.processing) {
        return;
    }

    notesForm.patch(
        route(
            'tutor.students.private-notes.update',
            props.student.id
        ),
        {
            preserveScroll: true,
        }
    );
};

/*
|--------------------------------------------------------------------------
| Homework
|--------------------------------------------------------------------------
*/

const submitHomework = () => {
    if (
        homeworkForm.processing
    ) {
        return;
    }

    homeworkForm.post(
        route(
            'tutor.assignments.store'
        ),
        {
            preserveScroll: true,
            forceFormData: true,

            onSuccess: () => {
                homeworkForm.reset(
                    'title',
                    'instructions',
                    'deadline',
                    'attachments'
                );

                const input =
                    document.getElementById(
                        'profile-attachments'
                    );

                if (input) {
                    input.value = '';
                }

                showHomeworkForm.value =
                    false;
            },
        }
    );
};

const handleAttachments = (
    event
) => {
    homeworkForm.attachments =
        Array.from(
            event.target.files ?? []
        );
};
</script>

<template>
    <Head :title="student.name" />

    <AuthenticatedLayout>
        <div
            class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6"
        >
            <!-- Back -->
            <div class="mb-5">
                <Link
                    :href="route('tutor.students')"
                    class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-slate-900"
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
                            d="m15 18-6-6 6-6"
                        />
                    </svg>

                    Back to students
                </Link>
            </div>

            <!-- Success -->
            <div
                v-if="successMessage"
                class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
            >
                {{ successMessage }}
            </div>

            <!-- Header -->
            <section
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-7 text-white"
                >
                    <div
                        class="flex flex-col gap-5 sm:flex-row sm:items-center"
                    >
                        <div
                            class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full border-4 border-white/20 bg-white/15 text-xl font-semibold"
                        >
                            {{ initials }}
                        </div>

                        <div
                            class="min-w-0 flex-1"
                        >
                            <p
                                class="text-sm font-medium text-indigo-100"
                            >
                                Student profile
                            </p>

                            <h1
                                class="mt-1 truncate text-2xl font-semibold md:text-3xl"
                            >
                                {{ student.name }}
                            </h1>

                            <p
                                class="mt-1 truncate text-sm text-indigo-100"
                            >
                                {{ student.email }}
                            </p>
                        </div>

                        <div
                            class="rounded-lg bg-white/10 px-4 py-3 backdrop-blur"
                        >
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-indigo-100"
                            >
                                Status
                            </p>

                            <div
                                class="mt-1 flex items-center gap-2 text-sm font-medium"
                            >
                                <span
                                    class="h-2 w-2 rounded-full bg-emerald-300"
                                ></span>

                                Active student
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Stats -->
            <div
                class="mt-6 grid gap-4 sm:grid-cols-4"
            >
                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <p
                        class="text-sm text-slate-500"
                    >
                        Total homework
                    </p>

                    <p
                        class="mt-2 text-2xl font-semibold text-slate-900"
                    >
                        {{ assignments.length }}
                    </p>
                </div>

                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <p
                        class="text-sm text-slate-500"
                    >
                        To do
                    </p>

                    <p
                        class="mt-2 text-2xl font-semibold text-indigo-600"
                    >
                        {{ todoCount }}
                    </p>
                </div>

                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <p
                        class="text-sm text-slate-500"
                    >
                        Awaiting review
                    </p>

                    <p
                        class="mt-2 text-2xl font-semibold text-amber-600"
                    >
                        {{ awaitingCount }}
                    </p>
                </div>

                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <p
                        class="text-sm text-slate-500"
                    >
                        Graded
                    </p>

                    <p
                        class="mt-2 text-2xl font-semibold text-emerald-600"
                    >
                        {{ gradedCount }}
                    </p>
                </div>
            </div>

            <!-- Main -->
            <div
                class="mt-6 grid gap-6 lg:grid-cols-5"
            >
                <!-- Left -->
                <div
                    class="space-y-6 lg:col-span-3"
                >
                    <!-- Progress -->
                    <section
                        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div
                            class="flex items-center justify-between gap-4"
                        >
                            <div>
                                <h2
                                    class="font-medium text-slate-900"
                                >
                                    Progress tracking
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500"
                                >
                                    Based on graded assignments.
                                </p>
                            </div>

                            <span
                                class="text-xl font-semibold text-slate-900"
                            >
                                {{ averageGrade }}%
                            </span>
                        </div>

                        <div
                            class="mt-5 h-2.5 overflow-hidden rounded-full bg-slate-100"
                        >
                            <div
                                class="h-full rounded-full bg-indigo-600 transition-all"
                                :style="{
                                    width:
                                        `${Math.min(
                                            averageGrade,
                                            100
                                        )}%`,
                                }"
                            ></div>
                        </div>

                        <p
                            v-if="gradedCount === 0"
                            class="mt-4 text-sm text-slate-400"
                        >
                            No graded assignments yet.
                        </p>
                    </section>

                    <!-- Homework -->
                    <section
                        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div
                            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <h2
                                    class="font-medium text-slate-900"
                                >
                                    Homework
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500"
                                >
                                    Assign and manage homework
                                    for this student.
                                </p>
                            </div>

                            <button
                                v-if="!showHomeworkForm"
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700"
                                @click="
                                    showHomeworkForm =
                                        true
                                "
                            >
                                <span>+</span>

                                Assign homework
                            </button>
                        </div>

                        <!-- Form -->
                        <form
                            v-if="showHomeworkForm"
                            class="mt-6 space-y-4 border-t border-slate-100 pt-5"
                            @submit.prevent="
                                submitHomework
                            "
                        >
                            <div
                                class="grid gap-4 sm:grid-cols-2"
                            >
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700"
                                    >
                                        Student
                                    </label>

                                    <input
                                        :value="
                                            student.name
                                        "
                                        disabled
                                        type="text"
                                        class="block w-full rounded-lg border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-500"
                                    />
                                </div>

                                <div>
                                    <label
                                        for="profile-subject"
                                        class="mb-1.5 block text-sm font-medium text-slate-700"
                                    >
                                        Subject
                                    </label>

                                    <select
                                        id="profile-subject"
                                        v-model="
                                            homeworkForm.subject
                                        "
                                        required
                                        :disabled="
                                            subjects.length ===
                                            0
                                        "
                                        class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm disabled:opacity-50 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                                    >
                                        <option
                                            value=""
                                            disabled
                                        >
                                            {{
                                                subjects.length
                                                    ? 'Select subject'
                                                    : 'No subject linked'
                                            }}
                                        </option>

                                        <option
                                            v-for="subject in subjects"
                                            :key="subject"
                                            :value="subject"
                                        >
                                            {{ subject }}
                                        </option>
                                    </select>

                                    <p
                                        v-if="
                                            homeworkForm
                                                .errors
                                                .subject
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{
                                            homeworkForm
                                                .errors
                                                .subject
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label
                                    for="profile-title"
                                    class="mb-1.5 block text-sm font-medium text-slate-700"
                                >
                                    Task title
                                </label>

                                <input
                                    id="profile-title"
                                    v-model="
                                        homeworkForm.title
                                    "
                                    type="text"
                                    required
                                    placeholder="e.g. Chapter 5 problem set"
                                    class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                                />
                            </div>

                            <div>
                                <label
                                    for="profile-instructions"
                                    class="mb-1.5 block text-sm font-medium text-slate-700"
                                >
                                    Instructions
                                </label>

                                <textarea
                                    id="profile-instructions"
                                    v-model="
                                        homeworkForm.instructions
                                    "
                                    rows="4"
                                    placeholder="Describe what the student needs to complete..."
                                    class="block w-full resize-none rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                                ></textarea>
                            </div>

                            <div>
                                <label
                                    for="profile-deadline"
                                    class="mb-1.5 block text-sm font-medium text-slate-700"
                                >
                                    Deadline
                                </label>

                                <input
                                    id="profile-deadline"
                                    v-model="
                                        homeworkForm.deadline
                                    "
                                    type="date"
                                    class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                                />
                            </div>

                            <div>
                                <label
                                    for="profile-attachments"
                                    class="mb-1.5 block text-sm font-medium text-slate-700"
                                >
                                    Attachments
                                </label>

                                <input
                                    id="profile-attachments"
                                    type="file"
                                    multiple
                                    accept=".pdf,.doc,.docx"
                                    class="block w-full rounded-lg border border-slate-200 bg-slate-50 text-sm text-slate-500 file:mr-3 file:border-0 file:border-r file:border-slate-200 file:bg-white file:px-3 file:py-2 file:text-sm"
                                    @change="
                                        handleAttachments
                                    "
                                />

                                <div
                                    v-if="
                                        homeworkForm
                                            .attachments
                                            .length
                                    "
                                    class="mt-2 space-y-1"
                                >
                                    <div
                                        v-for="file in homeworkForm.attachments"
                                        :key="
                                            file.name
                                        "
                                        class="rounded-md bg-slate-50 px-3 py-2 text-xs text-slate-600"
                                    >
                                        {{ file.name }}
                                    </div>
                                </div>
                            </div>

                            <div
                                class="flex justify-end gap-3 border-t border-slate-100 pt-5"
                            >
                                <button
                                    type="button"
                                    class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700"
                                    @click="
                                        showHomeworkForm =
                                            false
                                    "
                                >
                                    Cancel
                                </button>

                                <button
                                    type="submit"
                                    :disabled="
                                        homeworkForm.processing
                                        || subjects.length
                                            === 0
                                    "
                                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                                >
                                    {{
                                        homeworkForm.processing
                                            ? 'Assigning...'
                                            : 'Assign homework'
                                    }}
                                </button>
                            </div>
                        </form>

                        <!-- Real homework list -->
                        <div
                            v-if="
                                assignments.length
                                === 0
                            "
                            class="mt-6 flex min-h-36 flex-col items-center justify-center rounded-lg border border-dashed border-slate-200 px-6 text-center"
                        >
                            <p
                                class="text-sm font-medium text-slate-700"
                            >
                                No homework yet
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-400"
                            >
                                Assigned homework will
                                appear here.
                            </p>
                        </div>

                        <div
                            v-else
                            class="mt-6 space-y-3"
                        >
                            <article
                                v-for="assignment in assignments"
                                :key="
                                    assignment.id
                                "
                                class="rounded-xl border border-slate-200 p-4"
                            >
                                <div
                                    class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                                >
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
                                                    isOverdue(
                                                        assignment
                                                    )
                                                "
                                                class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700"
                                            >
                                                Overdue
                                            </span>
                                        </div>

                                        <h3
                                            class="mt-3 font-semibold text-slate-900"
                                        >
                                            {{
                                                assignment.title
                                            }}
                                        </h3>

                                        <p
                                            v-if="
                                                assignment.instructions
                                            "
                                            class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-500"
                                        >
                                            {{
                                                assignment.instructions
                                            }}
                                        </p>

                                        <p
                                            class="mt-3 text-xs text-slate-400"
                                        >
                                            Deadline:
                                            {{
                                                formatDate(
                                                    assignment.deadline
                                                )
                                            }}
                                        </p>

                                        <!-- Files -->
                                        <div
                                            v-if="
                                                assignment
                                                    .attachments
                                                    ?.length
                                            "
                                            class="mt-3 flex flex-wrap gap-2"
                                        >
                                            <a
                                                v-for="attachment in assignment.attachments"
                                                :key="
                                                    attachment.id
                                                "
                                                :href="
                                                    attachment.url
                                                "
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-indigo-50 hover:text-indigo-700"
                                            >
                                                📎
                                                {{
                                                    attachment.name
                                                }}
                                            </a>
                                        </div>

                                        <div
                                            v-if="
                                                assignment.feedback
                                            "
                                            class="mt-3 rounded-lg bg-indigo-50 p-3 text-sm text-indigo-800"
                                        >
                                            <span
                                                class="font-medium"
                                            >
                                                Feedback:
                                            </span>

                                            {{
                                                assignment.feedback
                                            }}
                                        </div>
                                    </div>

                                    <div
                                        v-if="
                                            assignment.grade
                                                !== null
                                            && assignment.grade
                                                !== undefined
                                        "
                                        class="shrink-0 rounded-lg bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700"
                                    >
                                        Grade:
                                        {{
                                            assignment.grade
                                        }}
                                    </div>
                                </div>
                            </article>
                        </div>
                    </section>
                </div>

                <!-- Right -->
                <div
                    class="space-y-6 lg:col-span-2"
                >
                    <!-- Notes -->
                    <section
                        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <h2
                            class="font-medium text-slate-900"
                        >
                            Private notes
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Visible only to you.
                        </p>

                        <textarea
                            v-model="
                                notesForm.private_notes
                            "
                            rows="9"
                            placeholder="Add private notes about this student..."
                            class="mt-5 block w-full resize-none rounded-lg border-slate-200 bg-slate-50 px-3 py-3 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                        ></textarea>

                        <p
                            v-if="
                                notesForm.errors
                                    .private_notes
                            "
                            class="mt-1 text-xs text-red-600"
                        >
                            {{
                                notesForm.errors
                                    .private_notes
                            }}
                        </p>

                        <button
                            type="button"
                            :disabled="
                                notesForm.processing
                            "
                            class="mt-4 inline-flex w-full items-center justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800 disabled:opacity-50"
                            @click="saveNotes"
                        >
                            {{
                                notesForm.processing
                                    ? 'Saving...'
                                    : 'Save notes'
                            }}
                        </button>
                    </section>

                    <!-- Details -->
                    <section
                        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <h2
                            class="font-medium text-slate-900"
                        >
                            Student details
                        </h2>

                        <div
                            class="mt-5 space-y-4 text-sm"
                        >
                            <div>
                                <p
                                    class="text-slate-400"
                                >
                                    Name
                                </p>

                                <p
                                    class="mt-1 font-medium text-slate-800"
                                >
                                    {{ student.name }}
                                </p>
                            </div>

                            <div
                                class="border-t border-slate-100 pt-4"
                            >
                                <p
                                    class="text-slate-400"
                                >
                                    Email
                                </p>

                                <p
                                    class="mt-1 break-all font-medium text-slate-800"
                                >
                                    {{ student.email }}
                                </p>
                            </div>

                            <div
                                class="border-t border-slate-100 pt-4"
                            >
                                <p
                                    class="text-slate-400"
                                >
                                    Subjects
                                </p>

                                <div
                                    v-if="
                                        subjects.length
                                    "
                                    class="mt-2 flex flex-wrap gap-2"
                                >
                                    <span
                                        v-for="subject in subjects"
                                        :key="
                                            subject
                                        "
                                        class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700"
                                    >
                                        {{ subject }}
                                    </span>
                                </div>

                                <p
                                    v-else
                                    class="mt-1 text-slate-500"
                                >
                                    No subject linked.
                                </p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>