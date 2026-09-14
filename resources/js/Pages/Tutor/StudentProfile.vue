<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    student: {
        type: Object,
        required: true,
    },

    privateNotes: {
        type: String,
        default: '',
    },
});

const page = usePage();

const successMessage = computed(
    () => page.props.flash?.success ?? null
);

const showHomeworkForm = ref(false);

const notesForm = useForm({
    private_notes: props.privateNotes,
});

const homeworkForm = useForm({
    student_id: props.student.id,
    subject: '',
    title: '',
    instructions: '',
    deadline: '',
    attachments: [],
});

const initials = computed(() => {
    return String(props.student.name ?? '')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
});

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

const submitHomework = () => {
    if (homeworkForm.processing) {
        return;
    }

    homeworkForm.post(
        route('tutor.assignments.store'),
        {
            preserveScroll: true,
            forceFormData: true,

            onSuccess: () => {
                homeworkForm.reset(
                    'subject',
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

                showHomeworkForm.value = false;
            },
        }
    );
};

const handleAttachments = (event) => {
    homeworkForm.attachments = Array.from(
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
            <!-- Top back link -->
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

            <!-- Student header -->
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

                        <div class="min-w-0 flex-1">
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

            <!-- Main content -->
            <div
                class="mt-6 grid gap-6 lg:grid-cols-5"
            >
                <!-- Left column -->
                <div class="space-y-6 lg:col-span-3">

                    <!-- Progress -->
                    <section
                        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div
                            class="flex items-start justify-between gap-4"
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
                                    Based on the last 10 graded
                                    assignments.
                                </p>
                            </div>

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
                                        d="M3 3v18h18"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m7 16 4-5 4 3 5-7"
                                    />
                                </svg>
                            </span>
                        </div>

                        <div class="mt-6">
                            <div
                                class="mb-2 flex items-center justify-between"
                            >
                                <span
                                    class="text-sm text-slate-500"
                                >
                                    Average grade
                                </span>

                                <span
                                    class="text-lg font-semibold text-slate-900"
                                >
                                    0%
                                </span>
                            </div>

                            <div
                                class="h-2.5 overflow-hidden rounded-full bg-slate-100"
                            >
                                <div
                                    class="h-full rounded-full bg-indigo-600"
                                    style="width: 0%"
                                ></div>
                            </div>

                            <div
                                class="mt-6 flex min-h-32 flex-col items-center justify-center rounded-lg border border-dashed border-slate-200 text-center"
                            >
                                <p
                                    class="text-sm font-medium text-slate-700"
                                >
                                    No graded assignments yet
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-400"
                                >
                                    Progress will appear after
                                    homework is graded.
                                </p>
                            </div>
                        </div>
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
                                    showHomeworkForm = true
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
                                    <path
                                        stroke-linecap="round"
                                        d="M12 5v14M5 12h14"
                                    />
                                </svg>

                                Assign homework
                            </button>
                        </div>

                        <!-- Homework form -->
                        <form
                            v-if="showHomeworkForm"
                            class="mt-6 space-y-4 border-t border-slate-100 pt-5"
                            @submit.prevent="submitHomework"
                        >
                            <div
                                class="grid gap-4 sm:grid-cols-2"
                            >
                                <!-- Student -->
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700"
                                    >
                                        Student
                                    </label>

                                    <input
                                        :value="student.name"
                                        type="text"
                                        disabled
                                        class="block w-full rounded-lg border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-500"
                                    />
                                </div>

                                <!-- Subject -->
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
                                        class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                                    >
                                        <option
                                            value=""
                                            disabled
                                        >
                                            Select subject
                                        </option>

                                        <option
                                            value="Mathematics"
                                        >
                                            Mathematics
                                        </option>

                                        <option
                                            value="English"
                                        >
                                            English
                                        </option>

                                        <option
                                            value="Physics"
                                        >
                                            Physics
                                        </option>

                                        <option
                                            value="Chemistry"
                                        >
                                            Chemistry
                                        </option>

                                        <option
                                            value="Biology"
                                        >
                                            Biology
                                        </option>
                                    </select>

                                    <p
                                        v-if="
                                            homeworkForm.errors
                                                .subject
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{
                                            homeworkForm.errors
                                                .subject
                                        }}
                                    </p>
                                </div>
                            </div>

                            <!-- Title -->
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
                                    class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                                />

                                <p
                                    v-if="
                                        homeworkForm.errors.title
                                    "
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{
                                        homeworkForm.errors
                                            .title
                                    }}
                                </p>
                            </div>

                            <!-- Instructions -->
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
                                    class="block w-full resize-none rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                                ></textarea>

                                <p
                                    v-if="
                                        homeworkForm.errors
                                            .instructions
                                    "
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{
                                        homeworkForm.errors
                                            .instructions
                                    }}
                                </p>
                            </div>

                            <!-- Deadline -->
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

                                <p
                                    v-if="
                                        homeworkForm.errors
                                            .deadline
                                    "
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{
                                        homeworkForm.errors
                                            .deadline
                                    }}
                                </p>
                            </div>

                            <!-- Attachments -->
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
                                    class="block w-full rounded-lg border border-slate-200 bg-slate-50 text-sm text-slate-500 file:mr-3 file:border-0 file:border-r file:border-slate-200 file:bg-white file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-50"
                                    @change="
                                        handleAttachments
                                    "
                                />

                                <p
                                    class="mt-1 text-xs text-slate-400"
                                >
                                    PDF or Word files. Maximum
                                    10 MB per file.
                                </p>

                                <div
                                    v-if="
                                        homeworkForm.attachments
                                            .length
                                    "
                                    class="mt-2 space-y-1"
                                >
                                    <div
                                        v-for="file in homeworkForm.attachments"
                                        :key="file.name"
                                        class="flex items-center gap-2 rounded-md bg-slate-50 px-2.5 py-2 text-xs text-slate-600"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-4 w-4 text-slate-400"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M21.44 11.05 12.25 20.24a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"
                                            />
                                        </svg>

                                        <span
                                            class="truncate"
                                        >
                                            {{ file.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div
                                class="flex justify-end gap-3 border-t border-slate-100 pt-5"
                            >
                                <button
                                    type="button"
                                    :disabled="
                                        homeworkForm.processing
                                    "
                                    class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50"
                                    @click="
                                        showHomeworkForm = false
                                    "
                                >
                                    Cancel
                                </button>

                                <button
                                    type="submit"
                                    :disabled="
                                        homeworkForm.processing
                                    "
                                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    {{
                                        homeworkForm.processing
                                            ? 'Assigning...'
                                            : 'Assign homework'
                                    }}
                                </button>
                            </div>
                        </form>

                        <!-- Homework list placeholder -->
                        <div
                            v-if="!showHomeworkForm"
                            class="mt-6 flex min-h-36 flex-col items-center justify-center rounded-lg border border-dashed border-slate-200 px-6 text-center"
                        >
                            <span
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400"
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

                            <p
                                class="mt-3 text-sm font-medium text-slate-700"
                            >
                                Homework will appear here
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-400"
                            >
                                Next we will connect this section
                                to assignments from the database.
                            </p>
                        </div>
                    </section>

                    <!-- Submissions -->
                    <section
                        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div
                            class="flex items-start justify-between gap-4"
                        >
                            <div>
                                <h2
                                    class="font-medium text-slate-900"
                                >
                                    Submissions & grading
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500"
                                >
                                    Review submitted homework,
                                    feedback and grades.
                                </p>
                            </div>

                            <span
                                class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600"
                            >
                                0 submissions
                            </span>
                        </div>

                        <div
                            class="mt-5 flex min-h-36 flex-col items-center justify-center rounded-lg border border-dashed border-slate-200 text-center"
                        >
                            <p
                                class="text-sm font-medium text-slate-700"
                            >
                                No submissions yet
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-400"
                            >
                                Student submissions will appear
                                here.
                            </p>
                        </div>
                    </section>
                </div>

                <!-- Right column -->
                <div class="space-y-6 lg:col-span-2">

                    <!-- Private notes -->
                    <section
                        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div
                            class="flex items-start justify-between gap-4"
                        >
                            <div>
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
                            </div>

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
                                    <rect
                                        width="18"
                                        height="11"
                                        x="3"
                                        y="11"
                                        rx="2"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M7 11V7a5 5 0 0 1 10 0v4"
                                    />
                                </svg>
                            </span>
                        </div>

                        <textarea
                            v-model="
                                notesForm.private_notes
                            "
                            rows="9"
                            placeholder="Add private notes about this student..."
                            class="mt-5 block w-full resize-none rounded-lg border-slate-200 bg-slate-50 px-3 py-3 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                        ></textarea>

                        <p
                            v-if="
                                notesForm.errors.private_notes
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

                    <!-- Student details -->
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
                                <p class="text-slate-400">
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
                                <p class="text-slate-400">
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
                                <p class="text-slate-400">
                                    Subjects
                                </p>

                                <p
                                    class="mt-1 text-slate-500"
                                >
                                    We will connect subjects from
                                    the tutor-student relationship
                                    next.
                                </p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>