<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,

    students: {
        type: Array,
        default: () => [],
    },
});

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
                'attachments',
            );
        },
    });
};

const handleAttachments = (event) => {
    form.attachments = Array.from(event.target.files ?? []);
};
</script>

<template>
    <Head title="Tutor Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Tutor Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Stats -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded bg-white p-4 shadow">
                        <p class="text-gray-500">
                            Active Students
                        </p>

                        <p class="text-xl font-bold">
                            {{ stats.activeStudents }}
                        </p>
                    </div>

                    <div class="rounded bg-white p-4 shadow">
                        <p class="text-gray-500">
                            Pending Reviews
                        </p>

                        <p class="text-xl font-bold">
                            {{ stats.pendingReviews }}
                        </p>
                    </div>
                </div>

                <!-- Homework -->
                <div class="mt-6 rounded bg-white p-6 shadow">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Assign new homework
                    </h3>

                    <form
                        class="mt-6 space-y-5"
                        @submit.prevent="submit"
                    >
                        <!-- Student + Subject -->
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                            <!-- Student -->
                            <div>
                                <label
                                    for="student"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Student
                                </label>

                                <select
                                    id="student"
                                    v-model="form.student_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                    v-if="form.errors.student_id"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.student_id }}
                                </p>
                            </div>

                            <!-- Subject -->
                            <div>
                                <label
                                    for="subject"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Subject
                                </label>

                                <select
                                    id="subject"
                                    v-model="form.subject"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="" disabled>
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
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.subject }}
                                </p>
                            </div>
                        </div>

                        <!-- Task title -->
                        <div>
                            <label
                                for="title"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Task title
                            </label>

                            <input
                                id="title"
                                v-model="form.title"
                                type="text"
                                required
                                placeholder="e.g. Chapter 5 problem set"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />

                            <p
                                v-if="form.errors.title"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.title }}
                            </p>
                        </div>

                        <!-- Instructions -->
                        <div>
                            <label
                                for="instructions"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Instructions
                            </label>

                            <textarea
                                id="instructions"
                                v-model="form.instructions"
                                rows="5"
                                placeholder="Describe what the student needs to complete..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            ></textarea>

                            <p
                                v-if="form.errors.instructions"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.instructions }}
                            </p>
                        </div>

                        <!-- Deadline -->
                        <div>
                            <label
                                for="deadline"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Deadline
                            </label>

                            <input
                                id="deadline"
                                v-model="form.deadline"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />

                            <p
                                v-if="form.errors.deadline"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.deadline }}
                            </p>
                        </div>

                        <!-- Attachments -->
                        <div>
                            <label
                                for="attachments"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Attachments
                            </label>

                            <input
                                id="attachments"
                                type="file"
                                multiple
                                accept=".pdf,.doc,.docx"
                                class="mt-1 block w-full text-sm text-gray-600"
                                @change="handleAttachments"
                            />

                            <p class="mt-1 text-xs text-gray-500">
                                PDF or Word files. Maximum 10 MB per file.
                            </p>

                            <p
                                v-if="form.errors.attachments"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.attachments }}
                            </p>

                            <ul
                                v-if="form.attachments.length"
                                class="mt-3 space-y-1 text-sm text-gray-600"
                            >
                                <li
                                    v-for="file in form.attachments"
                                    :key="file.name"
                                >
                                    {{ file.name }}
                                </li>
                            </ul>
                        </div>

                        <!-- Button -->
                        <div class="flex justify-end border-t border-gray-200 pt-5">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-md bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{
                                    form.processing
                                        ? 'Assigning...'
                                        : '+ Assign homework'
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>