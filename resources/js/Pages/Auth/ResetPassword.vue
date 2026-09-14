<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },

    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () =>
            form.reset(
                'password',
                'password_confirmation'
            ),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

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

                <path
                    stroke-linecap="round"
                    d="M12 15v2"
                />
            </svg>
        </div>

        <div class="mb-8">
            <p
                class="text-sm font-medium text-indigo-600"
            >
                Password recovery
            </p>

            <h1
                class="mt-1 text-3xl font-semibold tracking-tight text-slate-900"
            >
                Create a new password
            </h1>

            <p
                class="mt-2 text-sm leading-6 text-slate-500"
            >
                Choose a new secure password for your
                Tutorly account.
            </p>
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
                    class="block w-full rounded-xl border-slate-200 bg-slate-50 px-3.5 py-3 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="form.errors.email"
                />
            </div>

            <!-- Password -->
            <div>
                <label
                    for="password"
                    class="mb-1.5 block text-sm font-medium text-slate-700"
                >
                    New password
                </label>

                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Enter new password"
                    class="block w-full rounded-xl border-slate-200 bg-white px-3.5 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="form.errors.password"
                />
            </div>

            <!-- Confirmation -->
            <div>
                <label
                    for="password_confirmation"
                    class="mb-1.5 block text-sm font-medium text-slate-700"
                >
                    Confirm password
                </label>

                <input
                    id="password_confirmation"
                    v-model="
                        form.password_confirmation
                    "
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Repeat new password"
                    class="block w-full rounded-xl border-slate-200 bg-white px-3.5 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="
                        form.errors.password_confirmation
                    "
                />
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
                {{
                    form.processing
                        ? 'Resetting...'
                        : 'Reset password'
                }}
            </button>
        </form>

        <div
            class="mt-6 border-t border-slate-200 pt-6 text-center"
        >
            <Link
                :href="route('login')"
                class="text-sm font-medium text-indigo-600 transition hover:text-indigo-700"
            >
                ← Back to login
            </Link>
        </div>
    </GuestLayout>
</template>