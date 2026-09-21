<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';

import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';

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
    form.post(
        route('login'),
        {
            onFinish: () =>
                form.reset(
                    'password'
                ),
        }
    );
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <!-- Header -->
        <div>
            <h1
                class="text-2xl font-semibold tracking-tight text-slate-950"
            >
                Log in
            </h1>

            <p
                class="mt-2 text-sm leading-6 text-slate-500"
            >
                Enter your account details to continue.
            </p>
        </div>

        <!-- Status -->
        <div
            v-if="status"
            class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
        >
            {{ status }}
        </div>

        <!-- Form -->
        <form
            class="mt-6 space-y-5"
            @submit.prevent="submit"
        >
            <!-- Email -->
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
                    class="mt-2 block w-full rounded-md border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="form.errors.email"
                />
            </div>

            <!-- Password -->
            <div>
                <div
                    class="flex items-center justify-between gap-4"
                >
                    <label
                        for="password"
                        class="block text-sm font-medium text-slate-700"
                    >
                        Password
                    </label>

                    <Link
                        v-if="canResetPassword"
                        :href="
                            route(
                                'password.request'
                            )
                        "
                        class="text-xs font-medium text-slate-500 transition hover:text-slate-950"
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
                    class="mt-2 block w-full rounded-md border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="form.errors.password"
                />
            </div>

            <!-- Remember -->
            <label
                class="flex w-fit cursor-pointer items-center gap-2.5"
            >
                <input
                    v-model="form.remember"
                    type="checkbox"
                    class="rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                />

                <span
                    class="text-sm text-slate-600"
                >
                    Remember me
                </span>
            </label>

            <!-- Submit -->
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex w-full items-center justify-center rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-40"
            >
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
            <p
                class="text-sm text-slate-500"
            >
                Don't have an account?

                <Link
                    :href="
                        route(
                            'register'
                        )
                    "
                    class="font-medium text-slate-900 hover:underline"
                >
                    Create an account
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>