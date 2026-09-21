<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
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

const showInviteModal = ref(false);
const nameInput = ref(null);

const selectedStudent = ref(null);

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

const openInviteModal = async () => {
    showInviteModal.value = true;

    await nextTick();

    nameInput.value?.focus();
};

const closeInviteModal = () => {
    if (inviteForm.processing) {
        return;
    }

    showInviteModal.value = false;

    inviteForm.reset();
    inviteForm.clearErrors();
};

const submitInvitation = () => {
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
            closeInviteModal();
        },

        onError: () => {
            showInviteModal.value = true;
        },
    });
};

const openStudent = (student) => {
    selectedStudent.value = student;
};

const closeStudent = () => {
    selectedStudent.value = null;
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
    selectedStudent.value = null;

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

    removeForm.delete(
        route(
            'tutor.students.destroy',
            studentPendingRemoval.value.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                studentPendingRemoval.value = null;
            },

            onError: () => {
                removeError.value =
                    'Unable to remove this student.';
            },
        }
    );
};

const openDeleteInvitationModal = (invitation) => {
    invitationPendingDeletion.value = invitation;
    deleteInvitationError.value = null;
};

const closeDeleteInvitationModal = () => {
    if (deleteInvitationForm.processing) {
        return;
    }

    invitationPendingDeletion.value = null;
    deleteInvitationError.value = null;
};

const confirmDeleteInvitation = () => {
    if (
        !invitationPendingDeletion.value ||
        deleteInvitationForm.processing
    ) {
        return;
    }

    deleteInvitationForm.delete(
        route(
            'tutor.invitations.destroy',
            invitationPendingDeletion.value.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                invitationPendingDeletion.value = null;
            },

            onError: () => {
                deleteInvitationError.value =
                    'Unable to delete this invitation.';
            },
        }
    );
};

const formatDate = (value) => {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString(undefined, {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const formatDateTime = (value) => {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString(undefined, {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
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

    return `${Number(value).toFixed(2)} zł`;
};

const formatBilling = (value) => {
    if (!value) {
        return '—';
    }

    return String(value)
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) =>
            letter.toUpperCase()
        );
};
</script>

<template>
    <Head title="Students" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-5xl">
            <!-- Heading -->
            <header
                class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-950"
                    >
                        Students
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

                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800"
                    @click="openInviteModal"
                >
                    Invite student
                </button>
            </header>

            <!-- Success -->
            <div
                v-if="successMessage"
                class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ successMessage }}
            </div>

            <!-- New invitation -->
            <div
                v-if="flashedCode"
                class="mt-6 flex flex-col gap-4 border border-slate-200 bg-slate-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <p
                        class="text-sm font-medium text-slate-900"
                    >
                        Invitation created
                    </p>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Send this code to the student.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <span
                        class="font-mono text-lg font-semibold tracking-widest text-slate-900"
                    >
                        {{ flashedCode }}
                    </span>

                    <button
                        type="button"
                        class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        @click="copyCode(flashedCode)"
                    >
                        {{
                            copyFeedback === flashedCode
                                ? 'Copied'
                                : 'Copy'
                        }}
                    </button>
                </div>
            </div>

            <!-- Students -->
            <section class="mt-8">
                <div
                    v-if="students.length === 0"
                    class="border-y border-slate-200 py-12"
                >
                    <p
                        class="text-sm font-medium text-slate-900"
                    >
                        No students yet
                    </p>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Create an invitation code and send it
                        to your first student.
                    </p>

                    <button
                        type="button"
                        class="mt-4 text-sm font-medium text-slate-900 underline underline-offset-4"
                        @click="openInviteModal"
                    >
                        Invite student
                    </button>
                </div>

                <div
                    v-else
                    class="overflow-x-auto"
                >
                    <table
                        class="min-w-full text-left text-sm"
                    >
                        <thead
                            class="border-b border-slate-200 text-xs text-slate-500"
                        >
                            <tr>
                                <th
                                    class="pb-3 font-medium"
                                >
                                    Name
                                </th>

                                <th
                                    class="pb-3 font-medium"
                                >
                                    Subject
                                </th>

                                <th
                                    class="pb-3 font-medium"
                                >
                                    Lesson price
                                </th>

                                <th
                                    class="pb-3 font-medium"
                                >
                                    Joined
                                </th>

                                <th class="pb-3"></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="student in students"
                                :key="student.id"
                                class="cursor-pointer border-b border-slate-200 transition hover:bg-slate-50"
                                @click="openStudent(student)"
                            >
                                <td class="py-4 pr-6">
                                    <p
                                        class="font-medium text-slate-900"
                                    >
                                        {{ student.name }}
                                    </p>

                                    <p
                                        class="mt-0.5 text-xs text-slate-500"
                                    >
                                        {{ student.email }}
                                    </p>
                                </td>

                                <td
                                    class="py-4 pr-6 text-slate-600"
                                >
                                    {{
                                        student.subject ||
                                        '—'
                                    }}
                                </td>

                                <td
                                    class="py-4 pr-6 text-slate-600"
                                >
                                    {{
                                        formatPrice(
                                            student.lesson_price
                                        )
                                    }}
                                </td>

                                <td
                                    class="whitespace-nowrap py-4 pr-6 text-slate-500"
                                >
                                    {{
                                        formatDate(
                                            student.linked_at
                                        )
                                    }}
                                </td>

                                <td
                                    class="py-4 text-right text-slate-400"
                                >
                                    →
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Active invitations -->
            <section class="mt-12">
                <div
                    class="flex items-end justify-between border-b border-slate-200 pb-3"
                >
                    <div>
                        <h2
                            class="text-base font-semibold text-slate-900"
                        >
                            Active invitations
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Codes that have not been used yet.
                        </p>
                    </div>

                    <span
                        class="text-sm text-slate-500"
                    >
                        {{ invitations.length }}
                    </span>
                </div>

                <div
                    v-if="invitations.length === 0"
                    class="py-8 text-sm text-slate-500"
                >
                    No active invitations.
                </div>

                <div
                    v-else
                    class="overflow-x-auto"
                >
                    <table
                        class="min-w-full text-left text-sm"
                    >
                        <tbody>
                            <tr
                                v-for="invitation in invitations"
                                :key="invitation.id"
                                class="border-b border-slate-200"
                            >
                                <td class="py-4 pr-6">
                                    <span
                                        class="font-mono font-semibold tracking-wider text-slate-900"
                                    >
                                        {{ invitation.code }}
                                    </span>
                                </td>

                                <td
                                    class="py-4 pr-6 text-slate-700"
                                >
                                    {{
                                        invitation.student_name ||
                                        '—'
                                    }}
                                </td>

                                <td
                                    class="py-4 pr-6 text-slate-500"
                                >
                                    {{
                                        invitation.subject ||
                                        '—'
                                    }}
                                </td>

                                <td
                                    class="whitespace-nowrap py-4 pr-6 text-slate-500"
                                >
                                    {{
                                        formatDateTime(
                                            invitation.expires_at
                                        )
                                    }}
                                </td>

                                <td class="py-4 text-right">
                                    <div
                                        class="flex justify-end gap-3"
                                    >
                                        <button
                                            type="button"
                                            class="text-sm font-medium text-slate-600 hover:text-slate-950"
                                            @click="
                                                copyCode(
                                                    invitation.code
                                                )
                                            "
                                        >
                                            {{
                                                copyFeedback ===
                                                invitation.code
                                                    ? 'Copied'
                                                    : 'Copy'
                                            }}
                                        </button>

                                        <button
                                            type="button"
                                            class="text-sm font-medium text-red-600 hover:text-red-700"
                                            @click="
                                                openDeleteInvitationModal(
                                                    invitation
                                                )
                                            "
                                        >
                                            Revoke
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Student drawer -->
        <div
            v-if="selectedStudent"
            class="fixed inset-0 z-50"
        >
            <button
                type="button"
                aria-label="Close student details"
                class="absolute inset-0 bg-slate-950/20"
                @click="closeStudent"
            ></button>

            <aside
                class="absolute inset-y-0 right-0 w-full max-w-md overflow-y-auto border-l border-slate-200 bg-white p-6 shadow-xl"
            >
                <div
                    class="flex items-start justify-between gap-4"
                >
                    <div>
                        <h2
                            class="text-xl font-semibold text-slate-950"
                        >
                            {{ selectedStudent.name }}
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            {{ selectedStudent.email }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="text-sm text-slate-500 hover:text-slate-950"
                        @click="closeStudent"
                    >
                        Close
                    </button>
                </div>

                <dl class="mt-8 divide-y divide-slate-200">
                    <div class="py-4">
                        <dt
                            class="text-xs font-medium text-slate-500"
                        >
                            Subject
                        </dt>

                        <dd
                            class="mt-1 text-sm text-slate-900"
                        >
                            {{
                                selectedStudent.subject ||
                                '—'
                            }}
                        </dd>
                    </div>

                    <div class="py-4">
                        <dt
                            class="text-xs font-medium text-slate-500"
                        >
                            Lesson price
                        </dt>

                        <dd
                            class="mt-1 text-sm text-slate-900"
                        >
                            {{
                                formatPrice(
                                    selectedStudent.lesson_price
                                )
                            }}
                        </dd>
                    </div>

                    <div class="py-4">
                        <dt
                            class="text-xs font-medium text-slate-500"
                        >
                            Billing
                        </dt>

                        <dd
                            class="mt-1 text-sm text-slate-900"
                        >
                            {{
                                formatBilling(
                                    selectedStudent.billing_type
                                )
                            }}
                        </dd>
                    </div>

                    <div class="py-4">
                        <dt
                            class="text-xs font-medium text-slate-500"
                        >
                            Joined
                        </dt>

                        <dd
                            class="mt-1 text-sm text-slate-900"
                        >
                            {{
                                formatDate(
                                    selectedStudent.linked_at
                                )
                            }}
                        </dd>
                    </div>
                </dl>

                <div
                    class="mt-8 border-t border-slate-200 pt-6"
                >
                    <button
                        type="button"
                        class="text-sm font-medium text-red-600 hover:text-red-700"
                        @click="
                            openRemoveModal(
                                selectedStudent
                            )
                        "
                    >
                        Remove student
                    </button>
                </div>
            </aside>
        </div>

        <!-- Invite modal -->
        <Modal
            :show="showInviteModal"
            max-width="lg"
            @close="closeInviteModal"
        >
            <form
                class="p-6"
                @submit.prevent="submitInvitation"
            >
                <div>
                    <h2
                        class="text-lg font-semibold text-slate-950"
                    >
                        Invite student
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Create a code the student can use to
                        connect their account.
                    </p>
                </div>

                <div class="mt-6 space-y-4">
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
                            placeholder="Mark Kowalski"
                            class="block w-full rounded-md border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:ring-slate-500"
                        />

                        <InputError
                            class="mt-1.5"
                            :message="
                                inviteForm.errors.student_name
                            "
                        />
                    </div>

                    <div>
                        <label
                            for="invite-subject"
                            class="mb-1.5 block text-sm font-medium text-slate-700"
                        >
                            Subject
                        </label>

                        <input
                            id="invite-subject"
                            v-model="inviteForm.subject"
                            type="text"
                            required
                            placeholder="Mathematics"
                            class="block w-full rounded-md border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:ring-slate-500"
                        />

                        <InputError
                            class="mt-1.5"
                            :message="
                                inviteForm.errors.subject
                            "
                        />
                    </div>

                    <div>
                        <label
                            for="invite-price"
                            class="mb-1.5 block text-sm font-medium text-slate-700"
                        >
                            Lesson price
                        </label>

                        <div class="relative">
                            <input
                                id="invite-price"
                                v-model="inviteForm.price"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="80"
                                class="block w-full rounded-md border-slate-300 px-3 py-2 pr-12 text-sm focus:border-slate-500 focus:ring-slate-500"
                            />

                            <span
                                class="absolute inset-y-0 right-3 flex items-center text-sm text-slate-400"
                            >
                                PLN
                            </span>
                        </div>

                        <InputError
                            class="mt-1.5"
                            :message="
                                inviteForm.errors.price
                            "
                        />
                    </div>
                </div>

                <div
                    class="mt-7 flex justify-end gap-3"
                >
                    <button
                        type="button"
                        class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        @click="closeInviteModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        :disabled="inviteForm.processing"
                        class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-40"
                    >
                        {{
                            inviteForm.processing
                                ? 'Generating...'
                                : 'Generate code'
                        }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- Remove student -->
        <Modal
            :show="studentPendingRemoval !== null"
            max-width="md"
            @close="closeRemoveModal"
        >
            <div class="p-6">
                <h2
                    class="text-lg font-semibold text-slate-950"
                >
                    Remove student?
                </h2>

                <p class="mt-2 text-sm text-slate-600">
                    {{
                        studentPendingRemoval?.name
                    }}
                    will be disconnected from your account.
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
                        :disabled="removeForm.processing"
                        @click="confirmRemoveStudent"
                    >
                        {{
                            removeForm.processing
                                ? 'Removing...'
                                : 'Remove'
                        }}
                    </DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Revoke invitation -->
        <Modal
            :show="
                invitationPendingDeletion !== null
            "
            max-width="md"
            @close="closeDeleteInvitationModal"
        >
            <div class="p-6">
                <h2
                    class="text-lg font-semibold text-slate-950"
                >
                    Revoke invitation?
                </h2>

                <p class="mt-2 text-sm text-slate-600">
                    Code
                    <span
                        class="font-mono font-semibold"
                    >
                        {{
                            invitationPendingDeletion?.code
                        }}
                    </span>
                    will no longer work.
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
                        :disabled="
                            deleteInvitationForm.processing
                        "
                        @click="
                            confirmDeleteInvitation
                        "
                    >
                        {{
                            deleteInvitationForm.processing
                                ? 'Revoking...'
                                : 'Revoke'
                        }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>