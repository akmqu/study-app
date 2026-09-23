<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import {
    Head,
    useForm,
    usePage,
} from '@inertiajs/vue3';

import {
    computed,
    ref,
    watch,
} from 'vue';

const props = defineProps({
    relationships: {
        type: Array,
        default: () => [],
    },

    payments: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const successMessage = computed(
    () => page.props.flash?.success ?? null
);

const showCreateModal =
    ref(false);

const form = useForm({
    tutor_student_id: '',
    amount: '',
    period: '',
});

const selectedRelationship =
    computed(() => {
        return props.relationships.find(
            (relationship) =>
                String(
                    relationship.id
                ) ===
                String(
                    form.tutor_student_id
                )
        );
    });

watch(
    () => form.tutor_student_id,
    () => {
        if (
            selectedRelationship
                .value
                ?.lesson_price
        ) {
            form.amount =
                selectedRelationship
                    .value
                    .lesson_price;
        }
    }
);

const openCreateModal = () => {
    form.reset();
    form.clearErrors();

    showCreateModal.value =
        true;
};

const closeCreateModal = () => {
    if (form.processing) {
        return;
    }

    showCreateModal.value =
        false;

    form.reset();
    form.clearErrors();
};

const submitPayment = () => {
    if (form.processing) {
        return;
    }

    form.post(
        route(
            'tutor.payments.store'
        ),
        {
            preserveScroll:
                true,

            onSuccess: () => {
                showCreateModal.value =
                    false;

                form.reset();
                form.clearErrors();
            },
        }
    );
};

const formatMoney = (
    amount,
    currency = 'PLN'
) => {
    return new Intl.NumberFormat(
        'pl-PL',
        {
            style:
                'currency',

            currency:
                currency,
        }
    ).format(
        Number(amount)
    );
};

const formatDate = (
    value
) => {
    if (! value) {
        return '—';
    }

    return new Intl.DateTimeFormat(
        undefined,
        {
            year:
                'numeric',

            month:
                'short',

            day:
                'numeric',

            hour:
                '2-digit',

            minute:
                '2-digit',
        }
    ).format(
        new Date(value)
    );
};
</script>

<template>
    <Head title="Payments" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-6xl">
            <header
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-950"
                    >
                        Payments
                    </h1>

                    <p
                        class="mt-1.5 text-sm text-slate-500"
                    >
                        Create and track payment requests.
                    </p>
                </div>

                <button
                    type="button"
                    :disabled="
                        relationships.length ===
                        0
                    "
                    class="inline-flex items-center justify-center rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-40"
                    @click="
                        openCreateModal
                    "
                >
                    Create payment
                </button>
            </header>

            <div
                v-if="successMessage"
                class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{
                    successMessage
                }}
            </div>

            <section
                class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="border-b border-slate-200 px-5 py-4"
                >
                    <h2
                        class="text-base font-semibold text-slate-900"
                    >
                        Payment requests
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Payments requested from your students.
                    </p>
                </div>

                <div
                    v-if="
                        payments.length ===
                        0
                    "
                    class="px-5 py-10"
                >
                    <p
                        class="text-sm font-medium text-slate-900"
                    >
                        No payments yet
                    </p>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Create a payment request for a student.
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
                                    Period
                                </th>

                                <th
                                    class="px-5 py-3 text-xs font-medium text-slate-500"
                                >
                                    Amount
                                </th>

                                <th
                                    class="px-5 py-3 text-xs font-medium text-slate-500"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-5 py-3 text-xs font-medium text-slate-500"
                                >
                                    Created
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-100"
                        >
                            <tr
                                v-for="payment in payments"
                                :key="payment.id"
                            >
                                <td
                                    class="px-5 py-4"
                                >
                                    <p
                                        class="text-sm font-medium text-slate-900"
                                    >
                                        {{
                                            payment.student_name
                                        }}
                                    </p>

                                    <p
                                        class="mt-0.5 text-xs text-slate-500"
                                    >
                                        {{
                                            payment.student_email
                                        }}
                                    </p>
                                </td>

                                <td
                                    class="px-5 py-4 text-sm text-slate-600"
                                >
                                    {{
                                        payment.subject
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-sm text-slate-600"
                                >
                                    {{
                                        payment.period
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-sm font-medium text-slate-900"
                                >
                                    {{
                                        formatMoney(
                                            payment.amount,
                                            payment.currency
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4"
                                >
                                    <span
                                        v-if="
                                            payment.status ===
                                            'paid'
                                        "
                                        class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700"
                                    >
                                        Paid
                                    </span>

                                    <span
                                        v-else
                                        class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700"
                                    >
                                        Pending
                                    </span>
                                </td>

                                <td
                                    class="px-5 py-4 text-sm text-slate-500"
                                >
                                    {{
                                        formatDate(
                                            payment.created_at
                                        )
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div
            v-if="showCreateModal"
            class="fixed inset-0 z-[70]"
        >
            <button
                type="button"
                class="absolute inset-0 bg-slate-950/30"
                @click="
                    closeCreateModal
                "
            ></button>

            <div
                class="absolute inset-x-4 top-20 mx-auto max-w-lg rounded-xl bg-white shadow-xl"
            >
                <div
                    class="flex items-start justify-between border-b border-slate-200 px-6 py-5"
                >
                    <div>
                        <h2
                            class="text-lg font-semibold text-slate-950"
                        >
                            Create payment
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Request a payment from a student.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="text-sm text-slate-500 hover:text-slate-950"
                        @click="
                            closeCreateModal
                        "
                    >
                        Close
                    </button>
                </div>

                <form
                    class="space-y-5 p-6"
                    @submit.prevent="
                        submitPayment
                    "
                >
                    <div>
                        <label
                            for="payment-student"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Student
                        </label>

                        <select
                            id="payment-student"
                            v-model="
                                form.tutor_student_id
                            "
                            required
                            class="mt-2 block w-full rounded-md border-slate-300 bg-white"
                        >
                            <option
                                value=""
                                disabled
                            >
                                Select student
                            </option>

                            <option
                                v-for="relationship in relationships"
                                :key="
                                    relationship.id
                                "
                                :value="
                                    relationship.id
                                "
                            >
                                {{
                                    relationship.student_name
                                }}
                                —
                                {{
                                    relationship.subject
                                }}
                            </option>
                        </select>

                        <p
                            v-if="
                                form.errors.tutor_student_id
                            "
                            class="mt-1 text-xs text-red-600"
                        >
                            {{
                                form.errors.tutor_student_id
                            }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="payment-period"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Period
                        </label>

                        <input
                            id="payment-period"
                            v-model="
                                form.period
                            "
                            type="text"
                            required
                            placeholder="September 2026"
                            class="mt-2 block w-full rounded-md border-slate-300"
                        />

                        <p
                            v-if="
                                form.errors.period
                            "
                            class="mt-1 text-xs text-red-600"
                        >
                            {{
                                form.errors.period
                            }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="payment-amount"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Amount
                        </label>

                        <div
                            class="relative mt-2"
                        >
                            <input
                                id="payment-amount"
                                v-model="
                                    form.amount
                                "
                                type="number"
                                min="1"
                                step="0.01"
                                required
                                class="block w-full rounded-md border-slate-300 pr-14"
                            />

                            <span
                                class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-slate-500"
                            >
                                PLN
                            </span>
                        </div>

                        <p
                            v-if="
                                form.errors.amount
                            "
                            class="mt-1 text-xs text-red-600"
                        >
                            {{
                                form.errors.amount
                            }}
                        </p>
                    </div>

                    <div
                        class="flex justify-end gap-3 border-t border-slate-200 pt-5"
                    >
                        <button
                            type="button"
                            class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            @click="
                                closeCreateModal
                            "
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            :disabled="
                                form.processing
                            "
                            class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-40"
                        >
                            {{
                                form.processing
                                    ? 'Creating...'
                                    : 'Create payment'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>