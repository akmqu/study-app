<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';

import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'student',
});

const submit = () => {
    form.post(
        route('register'),
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
        <Head title="Register" />

        <!-- Header -->
        <div>
            <h1
                class="text-2xl font-semibold tracking-tight text-slate-950"
            >
                Create an account
            </h1>

            <p
                class="mt-2 text-sm leading-6 text-slate-500"
            >
                Choose your role and create your Tutorly account.
            </p>
        </div>

        <form
            class="mt-6 space-y-5"
            @submit.prevent="submit"
        >
            <!-- Role -->
            <div>
                <label
                    class="block text-sm font-medium text-slate-700"
                >
                    Account type
                </label>

                <div
                    class="mt-2 grid grid-cols-2 gap-3"
                >
                    <button
                        type="button"
                        class="rounded-lg border p-4 text-left transition"
                        :class="
                            form.role === 'student'
                                ? 'border-slate-900 bg-slate-50 ring-1 ring-slate-900'
                                : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50'
                        "
                        @click="
                            form.role =
                                'student'
                        "
                    >
                        <p
                            class="text-sm font-semibold text-slate-900"
                        >
                            Student
                        </p>

                        <p
                            class="mt-1 text-xs leading-5 text-slate-500"
                        >
                            Receive lessons and assignments.
                        </p>
                    </button>

                    <button
                        type="button"
                        class="rounded-lg border p-4 text-left transition"
                        :class="
                            form.role === 'tutor'
                                ? 'border-slate-900 bg-slate-50 ring-1 ring-slate-900'
                                : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50'
                        "
                        @click="
                            form.role =
                                'tutor'
                        "
                    >
                        <p
                            class="text-sm font-semibold text-slate-900"
                        >
                            Tutor
                        </p>

                        <p
                            class="mt-1 text-xs leading-5 text-slate-500"
                        >
                            Manage students and lessons.
                        </p>
                    </button>
                </div>

                <InputError
                    class="mt-1.5"
                    :message="form.errors.role"
                />
            </div>

            <!-- Name -->
            <div>
                <label
                    for="name"
                    class="block text-sm font-medium text-slate-700"
                >
                    Name
                </label>

                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Your name"
                    class="mt-2 block w-full rounded-md border-slate-300 bg-white px-3 py-2.5 text-sm placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="form.errors.name"
                />
            </div>

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
                    autocomplete="username"
                    placeholder="you@example.com"
                    class="mt-2 block w-full rounded-md border-slate-300 bg-white px-3 py-2.5 text-sm placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500"
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
                    class="block text-sm font-medium text-slate-700"
                >
                    Password
                </label>

                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Create a password"
                    class="mt-2 block w-full rounded-md border-slate-300 bg-white px-3 py-2.5 text-sm placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500"
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
                    placeholder="Repeat your password"
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
                class="inline-flex w-full items-center justify-center rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-40"
            >
                {{
                    form.processing
                        ? 'Creating account...'
                        : 'Create account'
                }}
            </button>
        </form>

        <div
            class="mt-6 border-t border-slate-200 pt-6 text-center"
        >
            <p
                class="text-sm text-slate-500"
            >
                Already have an account?

                <Link
                    :href="
                        route(
                            'login'
                        )
                    "
                    class="font-medium text-slate-900 hover:underline"
                >
                    Log in
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>