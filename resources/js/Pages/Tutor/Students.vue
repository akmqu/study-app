<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';

const props = defineProps({
    students: {
        type: Array,
        default: () => [],
    },

    invitations: {
        type: Array,
        default: () => [],
    },

    generatedCode: {
        type: String,
        default: null,
    },
});

const page = usePage();

const successMessage = computed(
    () => page.props.flash?.success ?? null
);

const flashedCode = computed(
    () =>
        page.props.flash?.generated_code ??
        props.generatedCode ??
        null
);

const showAddForm = ref(false);
const nameInput = ref(null);
const copyFeedback = ref(null);

const inviteForm = useForm({
    student_name: '',
    subject: '',
    price: '',
});

const studentPendingRemoval = ref(null);
const removeError = ref(null);
const removeForm = useForm({});

const invitationPendingDeletion = ref(null);
const deleteInvitationError = ref(null);
const deleteInvitationForm = useForm({});

const openAddForm = async () => {
    showAddForm.value = true;

    await nextTick();

    nameInput.value?.focus();
};

const closeAddForm = () => {
    if (inviteForm.processing) {
        return;
    }

    showAddForm.value = false;

    inviteForm.reset();
    inviteForm.clearErrors();
};

const submitGenerateCode = () => {
    if (inviteForm.processing) {
        return;
    }

    inviteForm.student_name = String(
        inviteForm.student_name ?? ''
    ).trim();

    inviteForm.subject = String(
        inviteForm.subject ?? ''
    ).trim();

    inviteForm.post(route('tutor.invitations.store'), {
        preserveScroll: true,

        onSuccess: () => {
            inviteForm.reset();
            inviteForm.clearErrors();

            showAddForm.value = false;
        },

        onError: () => {
            showAddForm.value = true;
        },
    });
};

const copyCode = async (code) => {
    try {
        await navigator.clipboard.writeText(code);

        copyFeedback.value = code;

        setTimeout(() => {
            if (copyFeedback.value === code) {
                copyFeedback.value = null;
            }
        }, 2000);
    } catch {
        copyFeedback.value = null;
    }
};

const openRemoveModal = (student) => {
    if (removeForm.processing) {
        return;
    }

    removeError.value = null;

    removeForm.clearErrors();

    studentPendingRemoval.value = student;
};

const closeRemoveModal = () => {
    if (removeForm.processing) {
        return;
    }

    studentPendingRemoval.value = null;
    removeError.value = null;

    removeForm.clearErrors();
};

const confirmRemoveStudent = () => {
    if (
        !studentPendingRemoval.value ||
        removeForm.processing
    ) {
        return;
    }

    removeError.value = null;

    removeForm.delete(
        route(
            'tutor.students.destroy',
            studentPendingRemoval.value.id
        ),
        {
            preserveScroll: true,
            replace: true,

            onSuccess: () => {
                studentPendingRemoval.value = null;
                removeError.value = null;
            },

            onError: () => {
                removeError.value =
                    'Unable to remove this student. Please try again.';
            },
        }
    );
};

const openDeleteInvitationModal = (invitation) => {
    if (deleteInvitationForm.processing) {
        return;
    }

    deleteInvitationError.value = null;

    deleteInvitationForm.clearErrors();

    invitationPendingDeletion.value = invitation;
};

const closeDeleteInvitationModal = () => {
    if (deleteInvitationForm.processing) {
        return;
    }

    invitationPendingDeletion.value = null;
    deleteInvitationError.value = null;

    deleteInvitationForm.clearErrors();
};

const confirmDeleteInvitation = () => {
    if (
        !invitationPendingDeletion.value ||
        deleteInvitationForm.processing
    ) {
        return;
    }

    deleteInvitationError.value = null;

    deleteInvitationForm.delete(
        route(
            'tutor.invitations.destroy',
            invitationPendingDeletion.value.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                invitationPendingDeletion.value = null;
                deleteInvitationError.value = null;
            },

            onError: () => {
                deleteInvitationError.value =
                    'Unable to delete this invitation code. Please try again.';
            },
        }
    );
};

const formatDateTime = (value) => {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatDate = (value) => {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatPrice = (value) => {
    if (
        value === null ||
        value === undefined ||
        value === ''
    ) {
        return '—';
    }

    return Number(value).toFixed(2);
};

const studentInitials = (name) => {
    return String(name ?? '')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
};
</script>

<template>
    <Head title="Students" />

    <AuthenticatedLayout>
        <div
            class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6"
        >
            <!-- Page heading -->
            <div
                class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900 md:text-3xl"
                    >
                        Students
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Manage your students and send invitation
                        codes to new learners.
                    </p>
                </div>

                <button
                    type="button"
                    :disabled="inviteForm.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700 disabled:opacity-50"
                    @click="openAddForm"
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

                    Add student
                </button>
            </div>

            <!-- Success -->
            <div
                v-if="successMessage"
                class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
            >
                {{ successMessage }}
            </div>

            <!-- Generated invitation -->
            <div
                v-if="flashedCode"
                class="mb-6 rounded-xl border border-indigo-200 bg-indigo-50 p-5"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <div class="flex items-center gap-2">
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white"
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
                                        d="M15 7h3a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-3"
                                    />
                                    <rect
                                        width="12"
                                        height="12"
                                        x="3"
                                        y="3"
                                        rx="2"
                                    />
                                </svg>
                            </span>

                            <p
                                class="text-sm font-medium text-indigo-950"
                            >
                                Invitation code created
                            </p>
                        </div>

                        <p
                            class="mt-2 text-sm text-indigo-700"
                        >
                            Send this code to the student so they
                            can connect their account with yours.
                        </p>
                    </div>

                    <div
                        class="flex items-center gap-3 rounded-lg bg-white px-4 py-3 shadow-sm"
                    >
                        <span
                            class="font-mono text-xl font-semibold tracking-widest text-slate-900"
                        >
                            {{ flashedCode }}
                        </span>

                        <button
                            type="button"
                            class="rounded-md border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50"
                            @click="copyCode(flashedCode)"
                        >
                            {{
                                copyFeedback === flashedCode
                                    ? 'Copied!'
                                    : 'Copy'
                            }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Add student -->
            <div
                v-if="showAddForm"
                class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div
                    class="mb-5 flex items-start justify-between gap-4"
                >
                    <div>
                        <h2
                            class="font-medium text-slate-900"
                        >
                            Invite a new student
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Enter the student's details and
                            generate an invitation code.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        @click="closeAddForm"
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
                                d="M6 6l12 12M18 6 6 18"
                            />
                        </svg>
                    </button>
                </div>

                <form
                    class="space-y-5"
                    @submit.prevent="submitGenerateCode"
                >
                    <div
                        class="grid gap-4 md:grid-cols-3"
                    >
                        <!-- Student name -->
                        <div>
                            <label
                                for="student_name"
                                class="mb-1.5 block text-sm font-medium text-slate-700"
                            >
                                Student name
                            </label>

                            <input
                                id="student_name"
                                ref="nameInput"
                                v-model="inviteForm.student_name"
                                type="text"
                                required
                                :disabled="inviteForm.processing"
                                placeholder="e.g. Alex Johnson"
                                class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm shadow-none placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                            />

                            <InputError
                                class="mt-1"
                                :message="
                                    inviteForm.errors.student_name
                                "
                            />
                        </div>

                        <!-- Subject -->
                        <div>
                            <label
                                for="subject"
                                class="mb-1.5 block text-sm font-medium text-slate-700"
                            >
                                Subject
                            </label>

                            <input
                                id="subject"
                                v-model="inviteForm.subject"
                                type="text"
                                required
                                :disabled="inviteForm.processing"
                                placeholder="e.g. Mathematics"
                                class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm shadow-none placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                            />

                            <InputError
                                class="mt-1"
                                :message="
                                    inviteForm.errors.subject
                                "
                            />
                        </div>

                        <!-- Price -->
                        <div>
                            <label
                                for="price"
                                class="mb-1.5 block text-sm font-medium text-slate-700"
                            >
                                Lesson price
                            </label>

                            <input
                                id="price"
                                v-model="inviteForm.price"
                                type="number"
                                min="0"
                                step="0.01"
                                :disabled="inviteForm.processing"
                                placeholder="Optional"
                                class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm shadow-none placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                            />

                            <InputError
                                class="mt-1"
                                :message="
                                    inviteForm.errors.price
                                "
                            />
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5"
                    >
                        <button
                            type="button"
                            :disabled="inviteForm.processing"
                            class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50"
                            @click="closeAddForm"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            :disabled="inviteForm.processing"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700 disabled:opacity-50"
                        >
                            <svg
                                v-if="!inviteForm.processing"
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
                                inviteForm.processing
                                    ? 'Generating...'
                                    : 'Generate code'
                            }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Summary -->
            <div
                class="mb-6 grid gap-4 sm:grid-cols-2"
            >
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

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-5 w-5 text-indigo-600"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
                            />
                            <circle cx="9" cy="7" r="4" />
                            <path
                                stroke-linecap="round"
                                d="M22 21v-2a4 4 0 0 0-3-3.87"
                            />
                        </svg>
                    </div>

                    <p
                        class="mt-3 text-3xl font-semibold text-slate-900"
                    >
                        {{ students.length }}
                    </p>
                </div>

                <div
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div
                        class="flex items-center justify-between"
                    >
                        <p
                            class="text-sm font-medium text-slate-500"
                        >
                            Pending invitations
                        </p>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-5 w-5 text-indigo-600"
                        >
                            <rect
                                width="18"
                                height="14"
                                x="3"
                                y="5"
                                rx="2"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m3 7 9 6 9-6"
                            />
                        </svg>
                    </div>

                    <p
                        class="mt-3 text-3xl font-semibold text-slate-900"
                    >
                        {{ invitations.length }}
                    </p>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Linked students -->
                <section
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="border-b border-slate-100 px-5 py-4"
                    >
                        <h2
                            class="font-medium text-slate-900"
                        >
                            Your students
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Open a student profile to view their
                            progress, notes and homework.
                        </p>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-if="students.length === 0"
                        class="flex flex-col items-center justify-center px-6 py-14 text-center"
                    >
                        <span
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-6 w-6"
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
                                    d="M19 8v6M22 11h-6"
                                />
                            </svg>
                        </span>

                        <h3
                            class="mt-4 text-sm font-medium text-slate-900"
                        >
                            No students yet
                        </h3>

                        <p
                            class="mt-1 max-w-sm text-sm text-slate-500"
                        >
                            Generate an invitation code and send
                            it to your first student.
                        </p>
                    </div>

                    <!-- Student list -->
                    <div
                        v-else
                        class="divide-y divide-slate-100"
                    >
                        <div
                            v-for="student in students"
                            :key="student.id"
                            class="flex flex-col gap-4 px-5 py-4 transition hover:bg-slate-50/70 sm:flex-row sm:items-center"
                        >
                            <div
                                class="flex min-w-0 flex-1 items-center gap-3"
                            >
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700"
                                >
                                    {{
                                        studentInitials(
                                            student.name
                                        )
                                    }}
                                </span>

                                <div class="min-w-0">
                                    <Link
                                        :href="
                                            route(
                                                'tutor.students.show',
                                                student.id
                                            )
                                        "
                                        class="block truncate text-sm font-medium text-slate-900 transition hover:text-indigo-600"
                                    >
                                        {{ student.name }}
                                    </Link>

                                    <p
                                        class="truncate text-sm text-slate-500"
                                    >
                                        {{ student.email }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="text-sm text-slate-500 sm:w-36"
                            >
                                <span
                                    class="block text-xs text-slate-400"
                                >
                                    Linked
                                </span>

                                {{
                                    formatDate(
                                        student.linked_at
                                    )
                                }}
                            </div>

                            <div
                                class="flex items-center gap-2"
                            >
                                <Link
                                    :href="
                                        route(
                                            'tutor.students.show',
                                            student.id
                                        )
                                    "
                                    class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                                >
                                    View profile
                                </Link>

                                <button
                                    type="button"
                                    :disabled="
                                        removeForm.processing &&
                                        studentPendingRemoval?.id ===
                                            student.id
                                    "
                                    class="rounded-lg px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 disabled:opacity-50"
                                    @click="
                                        openRemoveModal(
                                            student
                                        )
                                    "
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Active invitations -->
                <section
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-4"
                    >
                        <div>
                            <h2
                                class="font-medium text-slate-900"
                            >
                                Active invitation codes
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                Codes that students can still
                                redeem.
                            </p>
                        </div>

                        <span
                            class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700"
                        >
                            {{ invitations.length }} active
                        </span>
                    </div>

                    <div
                        v-if="invitations.length === 0"
                        class="px-6 py-10 text-center text-sm text-slate-500"
                    >
                        No active invitation codes.
                    </div>

                    <div
                        v-else
                        class="overflow-x-auto"
                    >
                        <table
                            class="min-w-full text-left text-sm"
                        >
                            <thead
                                class="border-b border-slate-100 bg-slate-50/70"
                            >
                                <tr>
                                    <th
                                        class="px-5 py-3 text-xs font-medium uppercase tracking-wide text-slate-500"
                                    >
                                        Code
                                    </th>

                                    <th
                                        class="px-5 py-3 text-xs font-medium uppercase tracking-wide text-slate-500"
                                    >
                                        Student
                                    </th>

                                    <th
                                        class="px-5 py-3 text-xs font-medium uppercase tracking-wide text-slate-500"
                                    >
                                        Subject
                                    </th>

                                    <th
                                        class="px-5 py-3 text-xs font-medium uppercase tracking-wide text-slate-500"
                                    >
                                        Price
                                    </th>

                                    <th
                                        class="px-5 py-3 text-xs font-medium uppercase tracking-wide text-slate-500"
                                    >
                                        Expires
                                    </th>

                                    <th
                                        class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wide text-slate-500"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-slate-100"
                            >
                                <tr
                                    v-for="invitation in invitations"
                                    :key="invitation.id"
                                    class="transition hover:bg-slate-50/70"
                                >
                                    <td class="px-5 py-4">
                                        <span
                                            class="rounded-md bg-slate-100 px-2.5 py-1.5 font-mono font-semibold tracking-wider text-slate-800"
                                        >
                                            {{
                                                invitation.code
                                            }}
                                        </span>
                                    </td>

                                    <td
                                        class="px-5 py-4 text-slate-700"
                                    >
                                        {{
                                            invitation.student_name ||
                                            '—'
                                        }}
                                    </td>

                                    <td
                                        class="px-5 py-4 text-slate-600"
                                    >
                                        {{
                                            invitation.subject ||
                                            '—'
                                        }}
                                    </td>

                                    <td
                                        class="px-5 py-4 text-slate-600"
                                    >
                                        {{
                                            formatPrice(
                                                invitation.price
                                            )
                                        }}
                                    </td>

                                    <td
                                        class="whitespace-nowrap px-5 py-4 text-slate-500"
                                    >
                                        {{
                                            formatDateTime(
                                                invitation.expires_at
                                            )
                                        }}
                                    </td>

                                    <td
                                        class="px-5 py-4"
                                    >
                                        <div
                                            class="flex justify-end gap-2"
                                        >
                                            <button
                                                type="button"
                                                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-100"
                                                @click="
                                                    copyCode(
                                                        invitation.code
                                                    )
                                                "
                                            >
                                                {{
                                                    copyFeedback ===
                                                    invitation.code
                                                        ? 'Copied!'
                                                        : 'Copy'
                                                }}
                                            </button>

                                            <button
                                                type="button"
                                                :disabled="
                                                    deleteInvitationForm.processing &&
                                                    invitationPendingDeletion?.id ===
                                                        invitation.id
                                                "
                                                class="rounded-lg px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50 disabled:opacity-50"
                                                @click="
                                                    openDeleteInvitationModal(
                                                        invitation
                                                    )
                                                "
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>

        <!-- Remove student modal -->
        <Modal
            :show="studentPendingRemoval !== null"
            max-width="md"
            @close="closeRemoveModal"
        >
            <div class="p-6">
                <h2
                    class="text-lg font-medium text-slate-900"
                >
                    Remove student?
                </h2>

                <p class="mt-2 text-sm text-slate-600">
                    Removing
                    <span class="font-medium text-slate-900">
                        {{ studentPendingRemoval?.name }}
                    </span>
                    only removes them from your student list.
                    Their account will not be deleted.
                </p>

                <InputError
                    class="mt-4"
                    :message="removeError"
                />

                <div
                    class="mt-6 flex justify-end gap-3"
                >
                    <SecondaryButton
                        :disabled="removeForm.processing"
                        @click="closeRemoveModal"
                    >
                        Cancel
                    </SecondaryButton>

                    <DangerButton
                        :class="{
                            'opacity-25':
                                removeForm.processing,
                        }"
                        :disabled="removeForm.processing"
                        @click="confirmRemoveStudent"
                    >
                        {{
                            removeForm.processing
                                ? 'Removing...'
                                : 'Remove student'
                        }}
                    </DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Delete invitation modal -->
        <Modal
            :show="invitationPendingDeletion !== null"
            max-width="md"
            @close="closeDeleteInvitationModal"
        >
            <div class="p-6">
                <h2
                    class="text-lg font-medium text-slate-900"
                >
                    Delete invitation code?
                </h2>

                <p class="mt-2 text-sm text-slate-600">
                    Code
                    <span
                        class="font-mono font-semibold text-slate-900"
                    >
                        {{
                            invitationPendingDeletion?.code
                        }}
                    </span>
                    will become invalid immediately and can no
                    longer be redeemed.
                </p>

                <InputError
                    class="mt-4"
                    :message="deleteInvitationError"
                />

                <div
                    class="mt-6 flex justify-end gap-3"
                >
                    <SecondaryButton
                        :disabled="
                            deleteInvitationForm.processing
                        "
                        @click="
                            closeDeleteInvitationModal
                        "
                    >
                        Cancel
                    </SecondaryButton>

                    <DangerButton
                        :class="{
                            'opacity-25':
                                deleteInvitationForm.processing,
                        }"
                        :disabled="
                            deleteInvitationForm.processing
                        "
                        @click="
                            confirmDeleteInvitation
                        "
                    >
                        {{
                            deleteInvitationForm.processing
                                ? 'Deleting...'
                                : 'Delete code'
                        }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>