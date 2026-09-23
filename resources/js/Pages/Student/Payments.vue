<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import {
    Head,
} from '@inertiajs/vue3';

const props = defineProps({
    payments: {
        type: Array,
        default: () => [],
    },
});

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
            <header>
                <h1
                    class="text-2xl font-semibold tracking-tight text-slate-950"
                >
                    Payments
                </h1>

                <p
                    class="mt-1.5 text-sm text-slate-500"
                >
                    View and pay requests from your tutors.
                </p>
            </header>

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
                        No payments
                    </p>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        You do not have any payment requests yet.
                    </p>
                </div>

                <div
                    v-else
                    class="divide-y divide-slate-100"
                >
                    <div
                        v-for="payment in payments"
                        :key="payment.id"
                        class="flex flex-col gap-5 px-5 py-5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <div
                                class="flex items-center gap-3"
                            >
                                <p
                                    class="text-sm font-semibold text-slate-900"
                                >
                                    {{
                                        payment.subject
                                    }}
                                </p>

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
                            </div>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                {{
                                    payment.tutor_name
                                }}
                                ·
                                {{
                                    payment.period
                                }}
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-400"
                            >
                                Created
                                {{
                                    formatDate(
                                        payment.created_at
                                    )
                                }}
                            </p>
                        </div>

                        <div
                            class="flex items-center gap-5"
                        >
                            <p
                                class="text-lg font-semibold text-slate-950"
                            >
                                {{
                                    formatMoney(
                                        payment.amount,
                                        payment.currency
                                    )
                                }}
                            </p>

                            <button
                                v-if="
                                    payment.status ===
                                    'pending'
                                "
                                type="button"
                                disabled
                                class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white opacity-50"
                            >
                                Pay with Stripe
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>