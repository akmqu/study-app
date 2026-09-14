<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <!-- Icon -->
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
                    width="18"
                    height="11"
                    x="3"
                    y="11"
                    rx="2"
                />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M7 11V7a5 5 0 0 1 10 0v4"
                />
            </svg>
        </div>

        <!-- Heading -->
        <div class="mb-8">
            <p
                class="text-sm font-medium text-indigo-600"
            >
                Password recovery
            </p>

            <h1
                class="mt-1 text-3xl font-semibold tracking-tight text-slate-900"
            >
                Forgot your password?
            </h1>

            <p
                class="mt-2 text-sm leading-6 text-slate-500"
            >
                Enter your email address and we'll send
                you a link to choose a new password.
            </p>
        </div>

        <!-- Status -->
        <div
            v-if="status"
            class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
        >
            {{ status }}
        </div>

        <form
            class="space-y-5"
            @submit.prevent="submit"
        >
            <div>
                <label
                    for="email"
                    class="mb-1.5 block text-sm font-medium text-slate-700"
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
                    class="block w-full rounded-xl border-slate-200 bg-white px-3.5 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="form.errors.email"
                />
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
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
                :href="route('login')"
                class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 transition hover:text-indigo-700"
            >
                <span>←</span>
                Back to login
            </Link>
        </div>
    </GuestLayout>
</template>