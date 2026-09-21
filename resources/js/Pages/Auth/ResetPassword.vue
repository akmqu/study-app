<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';

import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';

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
    form.post(
        route(
            'password.store'
        ),
        {
            onFinish: () =>
                form.reset(
                    'password',
                    'password_confirmation'
                ),
        }
    );
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <div>
            <h1
                class="text-2xl font-semibold tracking-tight text-slate-950"
            >
                Create a new password
            </h1>

            <p
                class="mt-2 text-sm leading-6 text-slate-500"
            >
                Choose a new password for your Tutorly account.
            </p>
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
                    class="mt-2 block w-full rounded-md border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-slate-500 focus:ring-slate-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="form.errors.email"
                />
            </div>

            <div>
                <label
                    for="password"
                    class="block text-sm font-medium text-slate-700"
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
                    class="mt-2 block w-full rounded-md border-slate-300 bg-white px-3 py-2.5 text-sm placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="form.errors.password"
                />
            </div>

            <div>
                <label
                    for="password_confirmation"
                    class="block text-sm font-medium text-slate-700"
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
                    class="mt-2 block w-full rounded-md border-slate-300 bg-white px-3 py-2.5 text-sm placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500"
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
                class="inline-flex w-full items-center justify-center rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800 disabled:opacity-40"
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