<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
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

            const input = document.getElementById(
                'dashboard-attachments'
            );

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
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-5xl">
            <!-- Page heading -->
            <header
                class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-950"
                    >
                        Dashboard
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        {{ students.length }}
                        {{
                            students.length === 1
                                ? 'student'
                                : 'students'
                        }}
                        connected
                    </p>
                </div>

                <Link
                    :href="route('tutor.students')"
                    class="text-sm font-medium text-slate-600 transition hover:text-slate-950"
                >
                    Manage students →
                </Link>
            </header>

            <!-- Success -->
            <div
                v-if="successMessage"
                class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ successMessage }}
            </div>

            <!-- Content -->
            <div
                class="mt-8 grid gap-10 lg:grid-cols-[minmax(0,1fr)_380px]"
            >
                <!-- Students -->
                <section>
                    <div
                        class="flex items-center justify-between"
                    >
                        <div>
                            <h2
                                class="text-base font-semibold text-slate-900"
                            >
                                Students
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                Students currently connected
                                to your account.
                            </p>
                        </div>
                    </div>

                    <!-- Empty -->
                    <div
                        v-if="students.length === 0"
                        class="mt-5 border-t border-slate-200 py-10"
                    >
                        <p
                            class="text-sm font-medium text-slate-900"
                        >
                            No students yet
                        </p>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Invite a student to start
                            assigning homework.
                        </p>

                        <Link
                            :href="route('tutor.students')"
                            class="mt-4 inline-block text-sm font-medium text-slate-900 underline underline-offset-4"
                        >
                            Go to students
                        </Link>
                    </div>

                    <!-- List -->
                    <div
                        v-else
                        class="mt-5 border-t border-slate-200"
                    >
                        <Link
                            v-for="student in students"
                            :key="student.id"
                            :href="
                                route(
                                    'tutor.students.show',
                                    student.id
                                )
                            "
                            class="group flex items-center justify-between border-b border-slate-200 py-4"
                        >
                            <div>
                                <p
                                    class="text-sm font-medium text-slate-900 group-hover:text-slate-600"
                                >
                                    {{ student.name }}
                                </p>
                            </div>

                            <span
                                class="text-sm text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-slate-700"
                            >
                                →
                            </span>
                        </Link>
                    </div>
                </section>

                <!-- Assignment form -->
                <section
                    class="self-start rounded-lg border border-slate-200 p-5"
                >
                    <div class="mb-5">
                        <h2
                            class="text-base font-semibold text-slate-900"
                        >
                            New assignment
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Create homework for a student.
                        </p>
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
                                class="block w-full rounded-md border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-500 focus:ring-slate-500"
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
                                class="mt-1.5 text-xs text-red-600"
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
                                class="block w-full rounded-md border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-500 focus:ring-slate-500"
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
                                class="mt-1.5 text-xs text-red-600"
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
                                Title
                            </label>

                            <input
                                id="title"
                                v-model="form.title"
                                type="text"
                                required
                                placeholder="Fractions worksheet"
                                class="block w-full rounded-md border-slate-300 bg-white px-3 py-2 text-sm placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500"
                            />

                            <p
                                v-if="form.errors.title"
                                class="mt-1.5 text-xs text-red-600"
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
                                placeholder="What should the student complete?"
                                class="block w-full resize-none rounded-md border-slate-300 bg-white px-3 py-2 text-sm placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500"
                            ></textarea>

                            <p
                                v-if="form.errors.instructions"
                                class="mt-1.5 text-xs text-red-600"
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
                                class="block w-full rounded-md border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-500 focus:ring-slate-500"
                            />

                            <p
                                v-if="form.errors.deadline"
                                class="mt-1.5 text-xs text-red-600"
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
                                class="block w-full text-sm text-slate-500 file:mr-3 file:rounded-md file:border file:border-slate-300 file:bg-white file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-50"
                                @change="handleAttachments"
                            />

                            <p
                                class="mt-1.5 text-xs text-slate-400"
                            >
                                PDF or Word, up to 10 MB.
                            </p>

                            <div
                                v-if="form.attachments.length"
                                class="mt-3 space-y-2"
                            >
                                <div
                                    v-for="file in form.attachments"
                                    :key="file.name"
                                    class="truncate text-xs text-slate-600"
                                >
                                    {{ file.name }}
                                </div>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="
                                form.processing ||
                                students.length === 0
                            "
                            class="inline-flex w-full items-center justify-center rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            {{
                                form.processing
                                    ? 'Creating...'
                                    : 'Create assignment'
                            }}
                        </button>
                    </form>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>