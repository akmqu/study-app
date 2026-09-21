<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';

import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(
        route(
            'password.email'
        )
    );
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <div>
            <h1
                class="text-2xl font-semibold tracking-tight text-slate-950"
            >
                Reset your password
            </h1>

            <p
                class="mt-2 text-sm leading-6 text-slate-500"
            >
                Enter your email address and we'll send you a password reset link.
            </p>
        </div>

        <div
            v-if="status"
            class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
        >
            {{ status }}
        </div>

        <form
            class="mt-6 space-y-5"
            @submit.prevent="submit"
        >
            <div>
                <label
                    for="email"
                    class="block text-sm font-medium text-slate-700"
                >
                    Email address
                </label>

                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="you@example.com"
                    class="mt-2 block w-full rounded-md border-slate-300 bg-white px-3 py-2.5 text-sm placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="form.errors.email"
                />
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex w-full items-center justify-center rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800 disabled:opacity-40"
            >
                {{
                    form.processing
                        ? 'Sending...'
                        : 'Send reset link'
                }}
            </button>
        </form>

        <div
            class="mt-6 border-t border-slate-200 pt-6 text-center"
        >
            <Link
                :href="
                    route(
                        'login'
                    )
                "
                class="text-sm font-medium text-slate-600 hover:text-slate-950"
            >
                ← Back to login
            </Link>
        </div>
    </GuestLayout>
</template>