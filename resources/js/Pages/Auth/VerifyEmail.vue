<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () =>
        props.status ===
        'verification-link-sent'
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div
            class="mb-6 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                class="h-6 w-6"
            >
                <rect
                    width="20"
                    height="16"
                    x="2"
                    y="4"
                    rx="2"
                />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m22 6-10 7L2 6"
                />
            </svg>
        </div>

        <div class="mb-8">
            <p
                class="text-sm font-medium text-indigo-600"
            >
                One last step
            </p>

            <h1
                class="mt-1 text-3xl font-semibold tracking-tight text-slate-900"
            >
                Verify your email
            </h1>

            <p
                class="mt-2 text-sm leading-6 text-slate-500"
            >
                We've sent a verification link to your
                email address. Open the message and click
                the link to activate your account.
            </p>
        </div>

        <!-- Success -->
        <div
            v-if="verificationLinkSent"
            class="mb-5 flex gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m9 12 2 2 4-4"
                />

                <circle
                    cx="12"
                    cy="12"
                    r="9"
                />
            </svg>

            <p
                class="text-sm font-medium text-emerald-700"
            >
                A new verification link has been sent
                to your email address.
            </p>
        </div>

        <div
            class="rounded-xl border border-slate-200 bg-white p-4"
        >
            <p
                class="text-sm leading-6 text-slate-500"
            >
                Didn't receive the email? You can request
                another verification link.
            </p>

            <form
                class="mt-4"
                @submit.prevent="submit"
            >
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{
                        form.processing
                            ? 'Sending...'
                            : 'Resend verification email'
                    }}
                </button>
            </form>
        </div>

        <div
            class="mt-6 border-t border-slate-200 pt-6 text-center"
        >
            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="text-sm font-medium text-slate-500 transition hover:text-slate-900"
            >
                Log out
            </Link>
        </div>
    </GuestLayout>
</template>