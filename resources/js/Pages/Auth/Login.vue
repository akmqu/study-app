<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },

    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <!-- Heading -->
        <div class="mb-8">
            <p class="text-sm font-medium text-indigo-600">
                Welcome back
            </p>

            <h1
                class="mt-1 text-3xl font-semibold tracking-tight text-slate-900"
            >
                Log in to Tutorly
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                Enter your account details to continue.
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
            <!-- Email -->
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

            <!-- Password -->
            <div>
                <div
                    class="mb-1.5 flex items-center justify-between"
                >
                    <label
                        for="password"
                        class="block text-sm font-medium text-slate-700"
                    >
                        Password
                    </label>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs font-medium text-indigo-600 transition hover:text-indigo-700"
                    >
                        Forgot password?
                    </Link>
                </div>

                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    class="block w-full rounded-xl border-slate-200 bg-white px-3.5 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="form.errors.password"
                />
            </div>

            <!-- Remember -->
            <label
                class="flex w-fit cursor-pointer items-center gap-2"
            >
                <Checkbox
                    name="remember"
                    v-model:checked="form.remember"
                />

                <span class="text-sm text-slate-600">
                    Remember me
                </span>
            </label>

            <!-- Login -->
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
                <svg
                    v-if="!form.processing"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-4 w-4"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"
                    />

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m10 17 5-5-5-5M15 12H3"
                    />
                </svg>

                {{
                    form.processing
                        ? 'Logging in...'
                        : 'Log in'
                }}
            </button>
        </form>

        <!-- Registration -->
        <div
            class="mt-6 border-t border-slate-200 pt-6 text-center"
        >
            <p class="text-sm text-slate-500">
                Don't have an account?

                <Link
                    :href="route('register')"
                    class="font-medium text-indigo-600 transition hover:text-indigo-700"
                >
                    Create an account
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>