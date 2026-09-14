<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password" />

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
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"
                />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m9 12 2 2 4-4"
                />
            </svg>
        </div>

        <div class="mb-8">
            <p
                class="text-sm font-medium text-indigo-600"
            >
                Security check
            </p>

            <h1
                class="mt-1 text-3xl font-semibold tracking-tight text-slate-900"
            >
                Confirm your password
            </h1>

            <p
                class="mt-2 text-sm leading-6 text-slate-500"
            >
                This is a secure area. Enter your password
                before continuing.
            </p>
        </div>

        <form
            class="space-y-5"
            @submit.prevent="submit"
        >
            <div>
                <label
                    for="password"
                    class="mb-1.5 block text-sm font-medium text-slate-700"
                >
                    Password
                </label>

                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autofocus
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    class="block w-full rounded-xl border-slate-200 bg-white px-3.5 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="form.errors.password"
                />
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
                {{
                    form.processing
                        ? 'Confirming...'
                        : 'Confirm password'
                }}
            </button>
        </form>
    </GuestLayout>
</template>