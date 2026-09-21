<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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

const showInviteModal = ref(false);
const selectedStudent = ref(null);

const studentPendingRemoval = ref(null);
const invitationPendingRemoval = ref(null);

const copyFeedback = ref(null);

const inviteForm = useForm({
    student_name: '',
    subject: '',
    price: '',
});

const inviteCode = computed(() => {
    return (
        props.generatedCode ??
        page.props.flash?.generated_code ??
        null
    );
});

const openInviteModal = () => {
    inviteForm.reset();
    inviteForm.clearErrors();

    showInviteModal.value = true;
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

    inviteForm.post(
        route('tutor.invitations.store'),
        {
            preserveScroll: true,

            onSuccess: () => {
                showInviteModal.value = false;

                inviteForm.reset();
                inviteForm.clearErrors();
            },
        }
    );
};

const openStudent = (student) => {
    selectedStudent.value = student;
};

const closeStudent = () => {
    selectedStudent.value = null;
};

const requestStudentRemoval = (student) => {
    studentPendingRemoval.value = student;
};

const cancelStudentRemoval = () => {
    studentPendingRemoval.value = null;
};

const confirmStudentRemoval = () => {
    if (!studentPendingRemoval.value) {
        return;
    }

    router.delete(
        route(
            'tutor.students.destroy',
            studentPendingRemoval.value.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                selectedStudent.value = null;
                studentPendingRemoval.value = null;
            },
        }
    );
};

const requestInvitationRemoval = (invitation) => {
    invitationPendingRemoval.value =
        invitation;
};

const cancelInvitationRemoval = () => {
    invitationPendingRemoval.value =
        null;
};

const confirmInvitationRemoval = () => {
    if (!invitationPendingRemoval.value) {
        return;
    }

    router.delete(
        route(
            'tutor.invitations.destroy',
            invitationPendingRemoval.value.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                invitationPendingRemoval.value =
                    null;
            },
        }
    );
};

const copyCode = async (code) => {
    try {
        await navigator.clipboard.writeText(
            code
        );

        copyFeedback.value = code;

        window.setTimeout(() => {
            if (
                copyFeedback.value ===
                code
            ) {
                copyFeedback.value =
                    null;
            }
        }, 1500);
    } catch {
        copyFeedback.value = null;
    }
};

const formatDate = (value) => {
    if (!value) {
        return '—';
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

const formatDateTime = (value) => {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat(
        undefined,
        {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }
    ).format(
        new Date(value)
    );
};

const formatPrice = (value) => {
    if (
        value === null ||
        value === undefined ||
        value === ''
    ) {
        return '—';
    }

    const numericValue =
        Number(value);

    if (
        Number.isNaN(
            numericValue
        )
    ) {
        return value;
    }

    return new Intl.NumberFormat(
        'pl-PL',
        {
            style: 'currency',
            currency: 'PLN',
        }
    ).format(
        numericValue
    );
};

const formatBilling = (value) => {
    if (value === 'per_lesson') {
        return 'Per lesson';
    }

    if (value === 'monthly') {
        return 'Monthly';
    }

    return '—';
};
</script>

<template>
    <Head title="Students" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-6xl">
            <!-- Header -->
            <header
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-950"
                    >
                        Students
                    </h1>

                    <p
                        class="mt-1.5 text-sm text-slate-500"
                    >
                        {{ students.length }}
                        {{
                            students.length === 1
                                ? 'student connected'
                                : 'students connected'
                        }}
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
                class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ successMessage }}
            </div>

            <!-- Generated invitation -->
            <section
                v-if="inviteCode"
                class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2
                            class="text-sm font-semibold text-slate-900"
                        >
                            Invitation created
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Send this code to the student.
                        </p>
                    </div>

                    <div
                        class="flex items-center gap-3"
                    >
                        <code
                            class="rounded-md bg-slate-100 px-3 py-2 font-mono text-base font-semibold tracking-widest text-slate-900"
                        >
                            {{ inviteCode }}
                        </code>

                        <button
                            type="button"
                            class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                            @click="
                                copyCode(
                                    inviteCode
                                )
                            "
                        >
                            {{
                                copyFeedback ===
                                inviteCode
                                    ? 'Copied'
                                    : 'Copy'
                            }}
                        </button>
                    </div>
                </div>
            </section>

            <!-- Students card -->
            <section
                class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="border-b border-slate-200 px-5 py-4"
                >
                    <h2
                        class="text-base font-semibold text-slate-900"
                    >
                        Connected students
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Students currently linked to your account.
                    </p>
                </div>

                <div
                    v-if="
                        students.length === 0
                    "
                    class="px-5 py-10"
                >
                    <p
                        class="text-sm font-medium text-slate-900"
                    >
                        No students yet
                    </p>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Invite a student to start managing lessons and assignments.
                    </p>
                </div>

                <div
                    v-else
                    class="overflow-x-auto"
                >
                    <table
                        class="min-w-full text-left"
                    >
                        <thead
                            class="bg-slate-50"
                        >
                            <tr
                                class="border-b border-slate-200"
                            >
                                <th
                                    class="px-5 py-3 text-xs font-medium text-slate-500"
                                >
                                    Name
                                </th>

                                <th
                                    class="px-5 py-3 text-xs font-medium text-slate-500"
                                >
                                    Subject
                                </th>

                                <th
                                    class="px-5 py-3 text-xs font-medium text-slate-500"
                                >
                                    Lesson price
                                </th>

                                <th
                                    class="px-5 py-3 text-xs font-medium text-slate-500"
                                >
                                    Joined
                                </th>

                                <th
                                    class="w-12 px-5 py-3"
                                ></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="student in students"
                                :key="student.id"
                                class="cursor-pointer border-b border-slate-100 transition last:border-b-0 hover:bg-slate-50"
                                @click="
                                    openStudent(
                                        student
                                    )
                                "
                            >
                                <td
                                    class="px-5 py-4"
                                >
                                    <p
                                        class="text-sm font-medium text-slate-900"
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
                                    class="px-5 py-4 text-sm text-slate-600"
                                >
                                    {{
                                        student.subject ||
                                        '—'
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-sm text-slate-600"
                                >
                                    {{
                                        formatPrice(
                                            student.lesson_price
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-sm text-slate-500"
                                >
                                    {{
                                        formatDate(
                                            student.linked_at
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right text-slate-400"
                                >
                                    →
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Invitations card -->
            <section
                class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4"
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
                        class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600"
                    >
                        {{ invitations.length }}
                    </span>
                </div>

                <div
                    v-if="
                        invitations.length === 0
                    "
                    class="px-5 py-10 text-sm text-slate-500"
                >
                    No active invitation codes.
                </div>

                <div
                    v-else
                    class="overflow-x-auto"
                >
                    <table
                        class="min-w-full text-left"
                    >
                        <thead
                            class="bg-slate-50"
                        >
                            <tr
                                class="border-b border-slate-200"
                            >
                                <th
                                    class="px-5 py-3 text-xs font-medium text-slate-500"
                                >
                                    Code
                                </th>

                                <th
                                    class="px-5 py-3 text-xs font-medium text-slate-500"
                                >
                                    Student
                                </th>

                                <th
                                    class="px-5 py-3 text-xs font-medium text-slate-500"
                                >
                                    Subject
                                </th>

                                <th
                                    class="px-5 py-3 text-xs font-medium text-slate-500"
                                >
                                    Expires
                                </th>

                                <th
                                    class="px-5 py-3"
                                ></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="invitation in invitations"
                                :key="invitation.id"
                                class="border-b border-slate-100 last:border-b-0"
                            >
                                <td
                                    class="px-5 py-4"
                                >
                                    <code
                                        class="font-mono text-sm font-semibold tracking-wider text-slate-900"
                                    >
                                        {{ invitation.code }}
                                    </code>
                                </td>

                                <td
                                    class="px-5 py-4 text-sm text-slate-600"
                                >
                                    {{
                                        invitation.student_name ||
                                        '—'
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-sm text-slate-600"
                                >
                                    {{
                                        invitation.subject ||
                                        '—'
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-sm text-slate-500"
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
                                                requestInvitationRemoval(
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
                aria-label="Close student"
                class="absolute inset-0 bg-slate-950/25"
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

                <dl
                    class="mt-8 divide-y divide-slate-200 border-y border-slate-200"
                >
                    <div
                        class="flex justify-between gap-4 py-4"
                    >
                        <dt
                            class="text-sm text-slate-500"
                        >
                            Subject
                        </dt>

                        <dd
                            class="text-sm font-medium text-slate-900"
                        >
                            {{
                                selectedStudent.subject ||
                                '—'
                            }}
                        </dd>
                    </div>

                    <div
                        class="flex justify-between gap-4 py-4"
                    >
                        <dt
                            class="text-sm text-slate-500"
                        >
                            Lesson price
                        </dt>

                        <dd
                            class="text-sm font-medium text-slate-900"
                        >
                            {{
                                formatPrice(
                                    selectedStudent.lesson_price
                                )
                            }}
                        </dd>
                    </div>

                    <div
                        class="flex justify-between gap-4 py-4"
                    >
                        <dt
                            class="text-sm text-slate-500"
                        >
                            Billing
                        </dt>

                        <dd
                            class="text-sm font-medium text-slate-900"
                        >
                            {{
                                formatBilling(
                                    selectedStudent.billing_type
                                )
                            }}
                        </dd>
                    </div>

                    <div
                        class="flex justify-between gap-4 py-4"
                    >
                        <dt
                            class="text-sm text-slate-500"
                        >
                            Joined
                        </dt>

                        <dd
                            class="text-sm font-medium text-slate-900"
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
                    class="mt-8"
                >
                    <button
                        type="button"
                        class="text-sm font-medium text-red-600 hover:text-red-700"
                        @click="
                            requestStudentRemoval(
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
        <div
            v-if="showInviteModal"
            class="fixed inset-0 z-[60]"
        >
            <button
                type="button"
                class="absolute inset-0 bg-slate-950/25"
                aria-label="Close"
                @click="closeInviteModal"
            ></button>

            <div
                class="absolute inset-x-4 top-16 mx-auto max-w-lg rounded-xl bg-white shadow-xl"
            >
                <div
                    class="flex items-start justify-between border-b border-slate-200 px-6 py-5"
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
                            Generate an invitation code for a student.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="text-sm text-slate-500 hover:text-slate-950"
                        @click="closeInviteModal"
                    >
                        Close
                    </button>
                </div>

                <form
                    class="space-y-5 p-6"
                    @submit.prevent="submitInvitation"
                >
                    <div>
                        <label
                            for="invite-name"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Student name
                        </label>

                        <input
                            id="invite-name"
                            v-model="inviteForm.student_name"
                            type="text"
                            required
                            class="mt-2 block w-full rounded-md border-slate-300"
                        />

                        <p
                            v-if="
                                inviteForm.errors.student_name
                            "
                            class="mt-1 text-xs text-red-600"
                        >
                            {{
                                inviteForm.errors.student_name
                            }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="invite-subject"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Subject
                        </label>

                        <input
                            id="invite-subject"
                            v-model="inviteForm.subject"
                            type="text"
                            required
                            class="mt-2 block w-full rounded-md border-slate-300"
                        />

                        <p
                            v-if="
                                inviteForm.errors.subject
                            "
                            class="mt-1 text-xs text-red-600"
                        >
                            {{
                                inviteForm.errors.subject
                            }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="invite-price"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Lesson price
                        </label>

                        <input
                            id="invite-price"
                            v-model="inviteForm.price"
                            type="number"
                            min="0"
                            step="0.01"
                            class="mt-2 block w-full rounded-md border-slate-300"
                        />

                        <p
                            v-if="
                                inviteForm.errors.price
                            "
                            class="mt-1 text-xs text-red-600"
                        >
                            {{
                                inviteForm.errors.price
                            }}
                        </p>
                    </div>

                    <div
                        class="flex justify-end gap-3 border-t border-slate-200 pt-5"
                    >
                        <button
                            type="button"
                            class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
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
                                    ? 'Creating...'
                                    : 'Generate code'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Remove student confirm -->
        <div
            v-if="studentPendingRemoval"
            class="fixed inset-0 z-[70]"
        >
            <button
                type="button"
                class="absolute inset-0 bg-slate-950/30"
                @click="cancelStudentRemoval"
            ></button>

            <div
                class="absolute inset-x-4 top-28 mx-auto max-w-md rounded-xl bg-white p-6 shadow-xl"
            >
                <h2
                    class="text-lg font-semibold text-slate-950"
                >
                    Remove student?
                </h2>

                <p
                    class="mt-2 text-sm leading-6 text-slate-500"
                >
                    {{
                        studentPendingRemoval.name
                    }}
                    will be removed from your student list.
                </p>

                <div
                    class="mt-6 flex justify-end gap-3"
                >
                    <button
                        type="button"
                        class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium"
                        @click="cancelStudentRemoval"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                        @click="confirmStudentRemoval"
                    >
                        Remove
                    </button>
                </div>
            </div>
        </div>

        <!-- Revoke invitation confirm -->
        <div
            v-if="invitationPendingRemoval"
            class="fixed inset-0 z-[70]"
        >
            <button
                type="button"
                class="absolute inset-0 bg-slate-950/30"
                @click="cancelInvitationRemoval"
            ></button>

            <div
                class="absolute inset-x-4 top-28 mx-auto max-w-md rounded-xl bg-white p-6 shadow-xl"
            >
                <h2
                    class="text-lg font-semibold text-slate-950"
                >
                    Revoke invitation?
                </h2>

                <p
                    class="mt-2 text-sm leading-6 text-slate-500"
                >
                    Code
                    <span
                        class="font-mono font-semibold text-slate-900"
                    >
                        {{
                            invitationPendingRemoval.code
                        }}
                    </span>
                    will stop working immediately.
                </p>

                <div
                    class="mt-6 flex justify-end gap-3"
                >
                    <button
                        type="button"
                        class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium"
                        @click="cancelInvitationRemoval"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                        @click="confirmInvitationRemoval"
                    >
                        Revoke
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>