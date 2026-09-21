<script setup>
import { computed } from 'vue';

import GuestLayout from '@/Layouts/GuestLayout.vue';

import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const verificationLinkSent =
    computed(() => {
        return (
            props.status ===
            'verification-link-sent'
        );
    });

const submit = () => {
    form.post(
        route(
            'verification.send'
        )
    );
};
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div>
            <h1
                class="text-2xl font-semibold tracking-tight text-slate-950"
            >
                Verify your email
            </h1>

            <p
                class="mt-2 text-sm leading-6 text-slate-500"
            >
                We sent a verification link to your email address.
                Open the message and follow the link to activate your account.
            </p>
        </div>

        <div
            v-if="verificationLinkSent"
            class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
        >
            A new verification link has been sent to your email address.
        </div>

        <div
            class="mt-6 rounded-lg border border-slate-200 bg-slate-50 p-4"
        >
            <p
                class="text-sm leading-6 text-slate-600"
            >
                Didn't receive the message? You can request another verification email.
            </p>

            <form
                class="mt-4"
                @submit.prevent="submit"
            >
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex w-full items-center justify-center rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800 disabled:opacity-40"
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
                :href="
                    route(
                        'logout'
                    )
                "
                method="post"
                as="button"
                class="text-sm font-medium text-slate-500 hover:text-slate-950"
            >
                Log out
            </Link>
        </div>
    </GuestLayout>
</template>