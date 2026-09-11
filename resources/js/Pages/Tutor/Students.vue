<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
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
const successMessage = computed(() => page.props.flash?.success ?? null);
const flashedCode = computed(
    () => page.props.flash?.generated_code ?? props.generatedCode ?? null,
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

    inviteForm.student_name = String(inviteForm.student_name ?? '').trim();
    inviteForm.subject = String(inviteForm.subject ?? '').trim();

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
    if (!studentPendingRemoval.value || removeForm.processing) {
        return;
    }

    removeError.value = null;

    removeForm.delete(
        route('tutor.students.destroy', studentPendingRemoval.value.id),
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
        },
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
    if (!invitationPendingDeletion.value || deleteInvitationForm.processing) {
        return;
    }

    deleteInvitationError.value = null;

    deleteInvitationForm.delete(
        route(
            'tutor.invitations.destroy',
            invitationPendingDeletion.value.id,
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
        },
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
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    return Number(value).toFixed(2);
};
</script>

<template>
    <Head title="Students" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Students
                </h2>

                <button
                    type="button"
                    class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"
                    :disabled="inviteForm.processing"
                    @click="openAddForm"
                >
                    Add student
                </button>
            </div>
        </template>

        <div class="min-h-screen bg-gray-100 p-6">

        <div
            v-if="successMessage"
            class="mb-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
        >
            {{ successMessage }}
        </div>

        <div
            v-if="flashedCode"
            class="mb-4 rounded border border-indigo-200 bg-indigo-50 px-4 py-3"
        >
            <p class="text-sm font-medium text-indigo-900">
                Share this code with your student
            </p>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <span
                    class="font-mono text-2xl font-semibold tracking-wider text-indigo-950"
                >
                    {{ flashedCode }}
                </span>
                <button
                    type="button"
                    class="rounded-md border border-indigo-300 bg-white px-3 py-1.5 text-sm font-medium text-indigo-800 transition hover:bg-indigo-100"
                    @click="copyCode(flashedCode)"
                >
                    {{
                        copyFeedback === flashedCode ? 'Copied!' : 'Copy code'
                    }}
                </button>
            </div>
        </div>

        <div
            v-if="showAddForm"
            class="mb-6 overflow-hidden rounded bg-white shadow"
        >
            <div class="border-b border-gray-100 px-4 py-3">
                <h2 class="text-lg font-semibold text-gray-900">
                    Add student
                </h2>
                <p class="text-sm text-gray-500">
                    Enter setup details, then generate a code for the student to
                    redeem on their account.
                </p>
            </div>

            <form class="space-y-4 p-4" @submit.prevent="submitGenerateCode">
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <InputLabel for="student_name" value="Student name" />
                        <TextInput
                            id="student_name"
                            ref="nameInput"
                            v-model="inviteForm.student_name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            :disabled="inviteForm.processing"
                        />
                        <InputError
                            class="mt-2"
                            :message="inviteForm.errors.student_name"
                        />
                    </div>

                    <div>
                        <InputLabel for="subject" value="Subject" />
                        <TextInput
                            id="subject"
                            v-model="inviteForm.subject"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            :disabled="inviteForm.processing"
                        />
                        <InputError
                            class="mt-2"
                            :message="inviteForm.errors.subject"
                        />
                    </div>

                    <div>
                        <InputLabel for="price" value="Payment / price" />
                        <TextInput
                            id="price"
                            v-model="inviteForm.price"
                            type="number"
                            min="0"
                            step="0.01"
                            class="mt-1 block w-full"
                            placeholder="Optional"
                            :disabled="inviteForm.processing"
                        />
                        <InputError
                            class="mt-2"
                            :message="inviteForm.errors.price"
                        />
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <PrimaryButton
                        type="submit"
                        :class="{ 'opacity-25': inviteForm.processing }"
                        :disabled="inviteForm.processing"
                    >
                        {{
                            inviteForm.processing
                                ? 'Generating…'
                                : 'Generate code'
                        }}
                    </PrimaryButton>
                    <SecondaryButton
                        type="button"
                        :disabled="inviteForm.processing"
                        @click="closeAddForm"
                    >
                        Cancel
                    </SecondaryButton>
                </div>
            </form>
        </div>

        <div class="mb-6 overflow-hidden rounded bg-white shadow">
            <div class="border-b border-gray-100 px-4 py-3">
                <h2 class="text-lg font-semibold text-gray-900">
                    Active invitation codes
                </h2>
                <p class="text-sm text-gray-500">
                    Pending codes that students can still redeem.
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-600">
                                Code
                            </th>
                            <th class="px-4 py-3 font-medium text-gray-600">
                                Student
                            </th>
                            <th class="px-4 py-3 font-medium text-gray-600">
                                Subject
                            </th>
                            <th class="px-4 py-3 font-medium text-gray-600">
                                Price
                            </th>
                            <th class="px-4 py-3 font-medium text-gray-600">
                                Expires
                            </th>
                            <th class="px-4 py-3 font-medium text-gray-600">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="invitations.length === 0">
                            <td
                                colspan="6"
                                class="px-4 py-8 text-center text-gray-500"
                            >
                                No active codes. Use Add student to generate
                                one.
                            </td>
                        </tr>
                        <tr
                            v-for="invitation in invitations"
                            :key="invitation.id"
                            class="hover:bg-gray-50"
                        >
                            <td
                                class="px-4 py-3 font-mono text-base font-semibold tracking-wider text-gray-900"
                            >
                                {{ invitation.code }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ invitation.student_name || '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ invitation.subject || '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ formatPrice(invitation.price) }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ formatDateTime(invitation.expires_at) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                                        @click="copyCode(invitation.code)"
                                    >
                                        {{
                                            copyFeedback === invitation.code
                                                ? 'Copied!'
                                                : 'Copy'
                                        }}
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md border border-red-200 bg-white px-3 py-1.5 text-sm font-medium text-red-700 transition hover:bg-red-50 disabled:opacity-50"
                                        :disabled="
                                            deleteInvitationForm.processing &&
                                            invitationPendingDeletion?.id ===
                                                invitation.id
                                        "
                                        @click="
                                            openDeleteInvitationModal(
                                                invitation,
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
        </div>

        <div class="overflow-hidden rounded bg-white shadow">
            <div class="border-b border-gray-100 px-4 py-3">
                <h2 class="text-lg font-semibold text-gray-900">
                    Linked students
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-600">
                                Name
                            </th>
                            <th class="px-4 py-3 font-medium text-gray-600">
                                Email
                            </th>
                            <th class="px-4 py-3 font-medium text-gray-600">
                                Linked
                            </th>
                            <th class="px-4 py-3 font-medium text-gray-600">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="students.length === 0">
                            <td
                                colspan="4"
                                class="px-4 py-8 text-center text-gray-500"
                            >
                                No linked students yet. Share an invitation
                                code to get started.
                            </td>
                        </tr>
                        <tr
                            v-for="student in students"
                            :key="student.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ student.name }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ student.email }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ formatDate(student.linked_at) }}
                            </td>
                            <td class="px-4 py-3">
                                <button
                                    type="button"
                                    class="rounded-md border border-red-200 bg-white px-3 py-1.5 text-sm font-medium text-red-700 transition hover:bg-red-50 disabled:opacity-50"
                                    :disabled="
                                        removeForm.processing &&
                                        studentPendingRemoval?.id === student.id
                                    "
                                    @click="openRemoveModal(student)"
                                >
                                    {{
                                        removeForm.processing &&
                                        studentPendingRemoval?.id === student.id
                                            ? 'Removing…'
                                            : 'Remove'
                                    }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Modal
            :show="studentPendingRemoval !== null"
            max-width="md"
            @close="closeRemoveModal"
        >
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Remove student?
                </h2>

                <p class="mt-2 text-sm text-gray-600">
                    Removing
                    <span class="font-medium text-gray-900">{{
                        studentPendingRemoval?.name
                    }}</span>
                    only removes them from your student list. Their account is
                    not deleted.
                </p>

                <InputError class="mt-4" :message="removeError" />

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton
                        :disabled="removeForm.processing"
                        @click="closeRemoveModal"
                    >
                        Cancel
                    </SecondaryButton>

                    <DangerButton
                        class="ms-0"
                        :class="{ 'opacity-25': removeForm.processing }"
                        :disabled="removeForm.processing"
                        @click="confirmRemoveStudent"
                    >
                        {{
                            removeForm.processing
                                ? 'Removing…'
                                : 'Remove student'
                        }}
                    </DangerButton>
                </div>
            </div>
        </Modal>

        <Modal
            :show="invitationPendingDeletion !== null"
            max-width="md"
            @close="closeDeleteInvitationModal"
        >
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Delete invitation code?
                </h2>

                <p class="mt-2 text-sm text-gray-600">
                    Code
                    <span class="font-mono font-semibold text-gray-900">{{
                        invitationPendingDeletion?.code
                    }}</span>
                    will become invalid immediately and can no longer be
                    redeemed.
                </p>

                <InputError class="mt-4" :message="deleteInvitationError" />

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton
                        :disabled="deleteInvitationForm.processing"
                        @click="closeDeleteInvitationModal"
                    >
                        Cancel
                    </SecondaryButton>

                    <DangerButton
                        class="ms-0"
                        :class="{
                            'opacity-25': deleteInvitationForm.processing,
                        }"
                        :disabled="deleteInvitationForm.processing"
                        @click="confirmDeleteInvitation"
                    >
                        {{
                            deleteInvitationForm.processing
                                ? 'Deleting…'
                                : 'Delete code'
                        }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </div>
    </AuthenticatedLayout>
</template>
