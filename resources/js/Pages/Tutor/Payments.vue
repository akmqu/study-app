<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import {
    Head,
    router,
    usePage,
} from '@inertiajs/vue3';

import {
    computed,
    reactive,
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

const billingForms =
    reactive(
        Object.fromEntries(
            props.relationships.map(
                (
                    relationship
                ) => [
                    relationship.id,
                    {
                        lesson_price:
                            relationship.lesson_price,

                        billing_type:
                            relationship.billing_type,

                        processing:
                            false,

                        errors:
                            {},
                    },
                ]
            )
        )
    );

const saveBilling = (
    relationship
) => {
    const form =
        billingForms[
            relationship.id
        ];

    if (
        ! form
        ||
        form.processing
    ) {
        return;
    }

    form.processing =
        true;

    form.errors =
        {};

    router.patch(
        route(
            'tutor.payments.billing.update',
            relationship.id
        ),
        {
            lesson_price:
                form.lesson_price,

            billing_type:
                form.billing_type,
        },
        {
            preserveScroll:
                true,

            onError: (
                errors
            ) => {
                form.errors =
                    errors;
            },

            onFinish: () => {
                form.processing =
                    false;
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

const billingLabel = (
    value
) => {
    if (
        value ===
        'per_lesson'
    ) {
        return 'After each lesson';
    }

    if (
        value ===
        'monthly'
    ) {
        return 'End of month';
    }

    return '—';
};
</script>

<template>
    <Head title="Payments" />

    <AuthenticatedLayout>
        <div
            class="mx-auto max-w-6xl"
        >
            <header>
                <h1
                    class="text-2xl font-semibold tracking-tight text-slate-950"
                >
                    Payments
                </h1>

                <p
                    class="mt-1.5 text-sm text-slate-500"
                >
                    Manage billing settings and track automatically generated payments.
                </p>
            </header>

            <div
                v-if="successMessage"
                class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{
                    successMessage
                }}
            </div>

            <!-- Billing settings -->
            <section
                class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="border-b border-slate-200 px-5 py-4"
                >
                    <h2
                        class="text-base font-semibold text-slate-900"
                    >
                        Billing settings
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Set the lesson price and payment schedule for each student and subject.
                    </p>
                </div>

                <div
                    v-if="
                        relationships.length ===
                        0
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
                        Billing settings will appear here after a student is linked to your account.
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
                                    Lesson price
                                </th>

                                <th
                                    class="px-5 py-3 text-xs font-medium text-slate-500"
                                >
                                    Billing schedule
                                </th>

                                <th
                                    class="px-5 py-3 text-right text-xs font-medium text-slate-500"
                                >
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-100"
                        >
                            <tr
                                v-for="relationship in relationships"
                                :key="
                                    relationship.id
                                "
                            >
                                <td
                                    class="px-5 py-4"
                                >
                                    <p
                                        class="text-sm font-medium text-slate-900"
                                    >
                                        {{
                                            relationship.student_name
                                        }}
                                    </p>

                                    <p
                                        class="mt-0.5 text-xs text-slate-500"
                                    >
                                        {{
                                            relationship.student_email
                                        }}
                                    </p>
                                </td>

                                <td
                                    class="px-5 py-4 text-sm text-slate-600"
                                >
                                    {{
                                        relationship.subject
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4"
                                >
                                    <div
                                        class="relative w-36"
                                    >
                                        <input
                                            v-model="
                                                billingForms[
                                                    relationship.id
                                                ].lesson_price
                                            "
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="block w-full rounded-md border-slate-300 pr-12 text-sm"
                                        />

                                        <span
                                            class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-slate-500"
                                        >
                                            PLN
                                        </span>
                                    </div>

                                    <p
                                        v-if="
                                            billingForms[
                                                relationship.id
                                            ].errors.lesson_price
                                        "
                                        class="mt-1 max-w-40 text-xs text-red-600"
                                    >
                                        {{
                                            billingForms[
                                                relationship.id
                                            ].errors.lesson_price
                                        }}
                                    </p>
                                </td>

                                <td
                                    class="px-5 py-4"
                                >
                                    <select
                                        v-model="
                                            billingForms[
                                                relationship.id
                                            ].billing_type
                                        "
                                        class="block w-48 rounded-md border-slate-300 bg-white text-sm"
                                    >
                                        <option
                                            value="per_lesson"
                                        >
                                            After each lesson
                                        </option>

                                        <option
                                            value="monthly"
                                        >
                                            End of month
                                        </option>
                                    </select>

                                    <p
                                        v-if="
                                            billingForms[
                                                relationship.id
                                            ].errors.billing_type
                                        "
                                        class="mt-1 max-w-48 text-xs text-red-600"
                                    >
                                        {{
                                            billingForms[
                                                relationship.id
                                            ].errors.billing_type
                                        }}
                                    </p>
                                </td>

                                <td
                                    class="px-5 py-4 text-right"
                                >
                                    <button
                                        type="button"
                                        :disabled="
                                            billingForms[
                                                relationship.id
                                            ].processing
                                        "
                                        class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-40"
                                        @click="
                                            saveBilling(
                                                relationship
                                            )
                                        "
                                    >
                                        {{
                                            billingForms[
                                                relationship.id
                                            ].processing
                                                ? 'Saving...'
                                                : 'Save'
                                        }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Payment history -->
            <section
                class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="border-b border-slate-200 px-5 py-4"
                >
                    <h2
                        class="text-base font-semibold text-slate-900"
                    >
                        Payment history
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Payments are generated automatically from completed lessons.
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
                        A payment will appear here automatically after a billable lesson is completed.
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
                                    Billing
                                </th>

                                <th
                                    class="px-5 py-3 text-xs font-medium text-slate-500"
                                >
                                    Lessons
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
                                :key="
                                    payment.id
                                "
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
                                    class="px-5 py-4 text-sm text-slate-600"
                                >
                                    {{
                                        billingLabel(
                                            payment.billing_type
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-sm text-slate-600"
                                >
                                    {{
                                        payment.lesson_count
                                        ?? '—'
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
    </AuthenticatedLayout>
</template>