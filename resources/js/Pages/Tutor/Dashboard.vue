<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },

    students: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const successMessage = computed(
    () => page.props.flash?.success ?? null
);

const form = useForm({
    student_id: '',
    subject: '',
    title: '',
    instructions: '',
    deadline: '',
    attachments: [],
});

const submit = () => {
    if (form.processing) {
        return;
    }

    form.post(route('tutor.assignments.store'), {
        preserveScroll: true,
        forceFormData: true,

        onSuccess: () => {
            form.reset(
                'student_id',
                'subject',
                'title',
                'instructions',
                'deadline',
                'attachments'
            );

            const input =
                document.getElementById('dashboard-attachments');

            if (input) {
                input.value = '';
            }
        },
    });
};

const handleAttachments = (event) => {
    form.attachments = Array.from(
        event.target.files ?? []
    );
};
</script>

<template>
    <Head title="Tutor Dashboard" />

    <AuthenticatedLayout>
        <div
            class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6"
        >
            <!-- Heading -->
            <div class="mb-8">
                <p
                    class="text-sm font-medium text-indigo-600"
                >
                    Tutor workspace
                </p>

                <h1
                    class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 md:text-3xl"
                >
                    Dashboard
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Assign homework, review submissions and
                    manage your students.
                </p>
            </div>

            <!-- Success message -->
            <div
                v-if="successMessage"
                class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
            >
                {{ successMessage }}
            </div>

            <!-- Stats -->
            <div
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <!-- Active students -->
                <div
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div
                        class="flex items-center justify-between"
                    >
                        <p
                            class="text-sm font-medium text-slate-500"
                        >
                            Active students
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
                                    d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
                                />

                                <circle
                                    cx="9"
                                    cy="7"
                                    r="4"
                                />

                                <path
                                    stroke-linecap="round"
                                    d="M22 21v-2a4 4 0 0 0-3-3.87"
                                />
                            </svg>
                        </span>
                    </div>

                    <p
                        class="mt-3 text-3xl font-semibold text-slate-900"
                    >
                        {{ stats.activeStudents ?? 0 }}
                    </p>

                    <p
                        class="mt-4 text-sm text-slate-400"
                    >
                        Students currently linked to you
                    </p>
                </div>

                <!-- Pending reviews -->
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
                        {{ stats.pendingReviews ?? 0 }}
                    </p>

                    <p
                        class="mt-4 text-sm text-slate-400"
                    >
                        Submissions waiting for grading
                    </p>
                </div>

                <!-- Assignments placeholder -->
                <div
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:col-span-2 lg:col-span-1"
                >
                    <div
                        class="flex items-center justify-between"
                    >
                        <p
                            class="text-sm font-medium text-slate-500"
                        >
                            Assignments
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
                                    d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"
                                />

                                <path
                                    stroke-linecap="round"
                                    d="M14 2v6h6M9 13h6M9 17h4"
                                />
                            </svg>
                        </span>
                    </div>

                    <p
                        class="mt-3 text-3xl font-semibold text-slate-900"
                    >
                        —
                    </p>

                    <p
                        class="mt-4 text-sm text-slate-400"
                    >
                        Assignment statistics later
                    </p>
                </div>
            </div>

            <!-- Main grid -->
            <div
                class="mt-6 grid gap-6 lg:grid-cols-5"
            >
                <!-- Submissions -->
                <section
                    class="rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-3"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-100 px-5 py-4"
                    >
                        <div>
                            <h2
                                class="font-medium text-slate-900"
                            >
                                Submissions to review
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                Homework submitted by your
                                students.
                            </p>
                        </div>

                        <span
                            class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700"
                        >
                            {{ stats.pendingReviews ?? 0 }}
                            pending
                        </span>
                    </div>

                    <div
                        class="flex min-h-96 flex-col items-center justify-center px-6 text-center"
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
                                    d="m9 11 3 3L22 4"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"
                                />
                            </svg>
                        </span>

                        <h3
                            class="mt-4 text-sm font-medium text-slate-900"
                        >
                            Submissions will appear here
                        </h3>

                        <p
                            class="mt-1 max-w-sm text-sm text-slate-500"
                        >
                            We will connect the real
                            submission and grading system
                            after the design pass.
                        </p>
                    </div>
                </section>

                <!-- Assign homework -->
                <section
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2"
                >
                    <div
                        class="mb-5 flex items-center gap-3"
                    >
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-white"
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
                                    d="M12 5v14M5 12h14"
                                />
                            </svg>
                        </span>

                        <div>
                            <h2
                                class="font-medium text-slate-900"
                            >
                                Assign new homework
                            </h2>

                            <p
                                class="text-sm text-slate-500"
                            >
                                Create an assignment
                            </p>
                        </div>
                    </div>

                    <form
                        class="space-y-4"
                        @submit.prevent="submit"
                    >
                        <!-- Student -->
                        <div>
                            <label
                                for="student"
                                class="mb-1.5 block text-sm font-medium text-slate-700"
                            >
                                Student
                            </label>

                            <select
                                id="student"
                                v-model="form.student_id"
                                required
                                class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
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

                            <p
                                v-if="form.errors.student_id"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.student_id }}
                            </p>
                        </div>

                        <!-- Subject -->
                        <div>
                            <label
                                for="subject"
                                class="mb-1.5 block text-sm font-medium text-slate-700"
                            >
                                Subject
                            </label>

                            <select
                                id="subject"
                                v-model="form.subject"
                                required
                                class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                            >
                                <option
                                    value=""
                                    disabled
                                >
                                    Select subject
                                </option>

                                <option value="Mathematics">
                                    Mathematics
                                </option>

                                <option value="English">
                                    English
                                </option>

                                <option value="Physics">
                                    Physics
                                </option>

                                <option value="Chemistry">
                                    Chemistry
                                </option>

                                <option value="Biology">
                                    Biology
                                </option>
                            </select>

                            <p
                                v-if="form.errors.subject"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.subject }}
                            </p>
                        </div>

                        <!-- Title -->
                        <div>
                            <label
                                for="title"
                                class="mb-1.5 block text-sm font-medium text-slate-700"
                            >
                                Task title
                            </label>

                            <input
                                id="title"
                                v-model="form.title"
                                type="text"
                                required
                                placeholder="e.g. Chapter 5 problem set"
                                class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2.5 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                            />

                            <p
                                v-if="form.errors.title"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.title }}
                            </p>
                        </div>

                        <!-- Instructions -->
                        <div>
                            <label
                                for="instructions"
                                class="mb-1.5 block text-sm font-medium text-slate-700"
                            >
                                Instructions
                            </label>

                            <textarea
                                id="instructions"
                                v-model="form.instructions"
                                rows="4"
                                placeholder="Describe what the student needs to complete..."
                                class="block w-full resize-none rounded-lg border-slate-200 bg-slate-50 px-3 py-2.5 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                            ></textarea>

                            <p
                                v-if="form.errors.instructions"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.instructions }}
                            </p>
                        </div>

                        <!-- Deadline -->
                        <div>
                            <label
                                for="deadline"
                                class="mb-1.5 block text-sm font-medium text-slate-700"
                            >
                                Deadline
                            </label>

                            <input
                                id="deadline"
                                v-model="form.deadline"
                                type="date"
                                class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                            />

                            <p
                                v-if="form.errors.deadline"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.deadline }}
                            </p>
                        </div>

                        <!-- Attachments -->
                        <div>
                            <label
                                for="dashboard-attachments"
                                class="mb-1.5 block text-sm font-medium text-slate-700"
                            >
                                Attachments
                            </label>

                            <input
                                id="dashboard-attachments"
                                type="file"
                                multiple
                                accept=".pdf,.doc,.docx"
                                class="block w-full rounded-lg border border-slate-200 bg-slate-50 text-sm text-slate-500 file:mr-3 file:border-0 file:border-r file:border-slate-200 file:bg-white file:px-3 file:py-2.5 file:text-sm file:font-medium file:text-slate-700"
                                @change="handleAttachments"
                            />

                            <p
                                class="mt-1 text-xs text-slate-400"
                            >
                                PDF or Word. Maximum 10 MB
                                per file.
                            </p>

                            <div
                                v-if="form.attachments.length"
                                class="mt-2 space-y-1"
                            >
                                <div
                                    v-for="file in form.attachments"
                                    :key="file.name"
                                    class="rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600"
                                >
                                    {{ file.name }}
                                </div>
                            </div>
                        </div>

                        <!-- Button -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <svg
                                v-if="!form.processing"
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

                            {{
                                form.processing
                                    ? 'Assigning...'
                                    : 'Assign homework'
                            }}
                        </button>
                    </form>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>