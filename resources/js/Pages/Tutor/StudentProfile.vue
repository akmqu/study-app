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

const successMessage = computed(() => page.props.flash?.success ?? null);

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

const saveNotes = () => {
    if (notesForm.processing) {
        return;
    }

    notesForm.patch(
        route('tutor.students.private-notes.update', props.student.id),
        {
            preserveScroll: true,
        },
    );
};

const submitHomework = () => {
    if (homeworkForm.processing) {
        return;
    }

    homeworkForm.post(route('tutor.assignments.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            homeworkForm.reset(
                'subject',
                'title',
                'instructions',
                'deadline',
                'attachments',
            );

            showHomeworkForm.value = false;
        },
    });
};

const handleAttachments = (event) => {
    homeworkForm.attachments = Array.from(event.target.files ?? []);
};
</script>

<template>
    <Head :title="student.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        {{ student.name }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        {{ student.email }}
                    </p>
                </div>

                <Link
                    :href="route('tutor.students')"
                    class="text-sm font-medium text-gray-600 hover:text-gray-900"
                >
                    ← Back to students
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <!-- Success message -->
                <div
                    v-if="successMessage"
                    class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"
                >
                    {{ successMessage }}
                </div>

                <!-- Student profile header -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="bg-gray-800 px-6 py-8 text-white">
                        <h1 class="text-2xl font-bold">
                            {{ student.name }}
                        </h1>

                        <p class="mt-2 text-sm text-gray-300">
                            {{ student.email }}
                        </p>
                    </div>
                </div>

                <!-- Progress -->
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Progress Tracking
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Progress will be calculated from the last 10 graded assignments.
                    </p>

                    <div class="mt-6">
                        <div class="mb-2 flex justify-between text-sm">
                            <span class="text-gray-600">Progress</span>
                            <span class="font-medium text-gray-900">0%</span>
                        </div>

                        <div class="h-3 overflow-hidden rounded-full bg-gray-200">
                            <div
                                class="h-full rounded-full bg-indigo-600"
                                style="width: 0%"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Private notes -->
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Private Notes
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        These notes are visible only to you and are not shown to the student.
                    </p>

                    <textarea
                        v-model="notesForm.private_notes"
                        rows="5"
                        class="mt-4 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Add private notes about this student..."
                    ></textarea>

                    <div class="mt-4 flex justify-end">
                        <button
                            type="button"
                            class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="notesForm.processing"
                            @click="saveNotes"
                        >
                            {{ notesForm.processing ? 'Saving...' : 'Save notes' }}
                        </button>
                    </div>
                </div>

                <!-- Homework -->
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Homework
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Assign and manage homework for this student.
                            </p>
                        </div>

                        <button
                            v-if="!showHomeworkForm"
                            type="button"
                            class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
    @click="showHomeworkForm = !showHomeworkForm"                        >
                            + Assign homework
                        </button>
                    </div>

                    <!-- Homework form -->
                    <form
                        v-if="showHomeworkForm"
                        class="mt-6 space-y-5 border-t border-gray-200 pt-6"
                        @submit.prevent="submitHomework"
                    >
                        <!-- Student + Subject -->
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <label
                                    for="student"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Student
                                </label>

                                <input
                                    id="student"
                                    type="text"
                                    :value="student.name"
                                    disabled
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-600 shadow-sm"
                                />
                            </div>

                            <div>
                                <label
                                    for="subject"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Subject
                                </label>

                                <select
                                    id="subject"
                                    v-model="homeworkForm.subject"
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
                                    v-if="homeworkForm.errors.subject"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ homeworkForm.errors.subject }}
                                </p>
                            </div>
                        </div>

                        <!-- Title -->
                        <div>
                            <label
                                for="title"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Task title
                            </label>

                            <input
                                id="title"
                                v-model="homeworkForm.title"
                                type="text"
                                placeholder="e.g. Chapter 5 problem set"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />

                            <p
                                v-if="homeworkForm.errors.title"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ homeworkForm.errors.title }}
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
                                v-model="homeworkForm.instructions"
                                rows="5"
                                placeholder="Describe what the student needs to complete..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            ></textarea>

                            <p
                                v-if="homeworkForm.errors.instructions"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ homeworkForm.errors.instructions }}
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
                                v-model="homeworkForm.deadline"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />

                            <p
                                v-if="homeworkForm.errors.deadline"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ homeworkForm.errors.deadline }}
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
                                v-if="homeworkForm.errors.attachments"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ homeworkForm.errors.attachments }}
                            </p>

                            <ul
                                v-if="homeworkForm.attachments.length"
                                class="mt-3 space-y-1 text-sm text-gray-600"
                            >
                                <li
                                    v-for="file in homeworkForm.attachments"
                                    :key="file.name"
                                >
                                    {{ file.name }}
                                </li>
                            </ul>
                        </div>

                        <!-- Form actions -->
                        <div class="flex justify-end gap-3 border-t border-gray-200 pt-5">
                            <button
                                type="button"
                                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                                :disabled="homeworkForm.processing"
                                @click="showHomeworkForm = false"
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700 disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="homeworkForm.processing"
                            >
                                {{
                                    homeworkForm.processing
                                        ? 'Assigning...'
                                        : '+ Assign homework'
                                }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Submissions -->
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Submissions & Grading
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Submitted homework and grades will appear here.
                    </p>

                    <div class="mt-6 rounded-lg border border-dashed border-gray-300 p-8 text-center">
                        <p class="text-sm text-gray-500">
                            No submissions yet.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>