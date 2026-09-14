<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'student',
});

const submit = () => {
    form.post(route('register'), {
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
        <Head title="Register" />

        <!-- Heading -->
        <div class="mb-8">
            <p class="text-sm font-medium text-indigo-600">
                Get started
            </p>

            <h1
                class="mt-1 text-3xl font-semibold tracking-tight text-slate-900"
            >
                Create your Tutorly account
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                Choose your role and create an account
                to start learning or teaching.
            </p>
        </div>

        <form
            class="space-y-5"
            @submit.prevent="submit"
        >
            <!-- Name -->
            <div>
                <label
                    for="name"
                    class="mb-1.5 block text-sm font-medium text-slate-700"
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
                    class="block w-full rounded-xl border-slate-200 bg-white px-3.5 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500"
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
                    class="mb-1.5 block text-sm font-medium text-slate-700"
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
                    class="block w-full rounded-xl border-slate-200 bg-white px-3.5 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="form.errors.email"
                />
            </div>

            <!-- Role -->
            <div>
                <label
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    I am a...
                </label>

                <div class="grid grid-cols-2 gap-3">
                    <!-- Student -->
                    <button
                        type="button"
                        class="rounded-xl border p-4 text-left transition"
                        :class="
                            form.role === 'student'
                                ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500'
                                : 'border-slate-200 bg-white hover:bg-slate-50'
                        "
                        @click="form.role = 'student'"
                    >
                        <div
                            class="flex items-center gap-3"
                        >
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-lg"
                                :class="
                                    form.role === 'student'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-slate-100 text-slate-500'
                                "
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m3 10 9-5 9 5-9 5Z"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M7 12v5c3 2 7 2 10 0v-5"
                                    />
                                </svg>
                            </span>

                            <div>
                                <p
                                    class="text-sm font-medium text-slate-900"
                                >
                                    Student
                                </p>

                                <p
                                    class="mt-0.5 text-xs text-slate-500"
                                >
                                    Learn with tutors
                                </p>
                            </div>
                        </div>
                    </button>

                    <!-- Tutor -->
                    <button
                        type="button"
                        class="rounded-xl border p-4 text-left transition"
                        :class="
                            form.role === 'tutor'
                                ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500'
                                : 'border-slate-200 bg-white hover:bg-slate-50'
                        "
                        @click="form.role = 'tutor'"
                    >
                        <div
                            class="flex items-center gap-3"
                        >
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-lg"
                                :class="
                                    form.role === 'tutor'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-slate-100 text-slate-500'
                                "
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
                                    />

                                    <circle
                                        cx="9"
                                        cy="7"
                                        r="4"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        d="M19 8v6M22 11h-6"
                                    />
                                </svg>
                            </span>

                            <div>
                                <p
                                    class="text-sm font-medium text-slate-900"
                                >
                                    Tutor
                                </p>

                                <p
                                    class="mt-0.5 text-xs text-slate-500"
                                >
                                    Teach students
                                </p>
                            </div>
                        </div>
                    </button>
                </div>

                <InputError
                    class="mt-1.5"
                    :message="form.errors.role"
                />
            </div>

            <!-- Password -->
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
                    autocomplete="new-password"
                    placeholder="Create a password"
                    class="block w-full rounded-xl border-slate-200 bg-white px-3.5 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="form.errors.password"
                />
            </div>

            <!-- Confirm -->
            <div>
                <label
                    for="password_confirmation"
                    class="mb-1.5 block text-sm font-medium text-slate-700"
                >
                    Confirm password
                </label>

                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Repeat your password"
                    class="block w-full rounded-xl border-slate-200 bg-white px-3.5 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="
                        form.errors.password_confirmation
                    "
                />
            </div>

            <!-- Submit -->
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
                {{
                    form.processing
                        ? 'Creating account...'
                        : 'Create account'
                }}
            </button>
        </form>

        <!-- Login -->
        <div
            class="mt-6 border-t border-slate-200 pt-6 text-center"
        >
            <p class="text-sm text-slate-500">
                Already have an account?

                <Link
                    :href="route('login')"
                    class="font-medium text-indigo-600 transition hover:text-indigo-700"
                >
                    Log in
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>