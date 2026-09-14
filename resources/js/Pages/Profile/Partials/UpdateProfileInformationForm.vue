<script setup>
import InputError from '@/Components/InputError.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },

    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <div class="flex items-start gap-3">
            <span
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
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
                        d="M20 21a8 8 0 0 0-16 0"
                    />
                    <circle cx="12" cy="7" r="4" />
                </svg>
            </span>

            <div>
                <h2 class="font-medium text-slate-900">
                    Profile information
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Update your name and email address.
                </p>
            </div>
        </div>

        <form
            class="mt-6 max-w-xl space-y-5"
            @submit.prevent="form.patch(route('profile.update'))"
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
                    class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 shadow-none placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
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
                    Email
                </label>

                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="username"
                    class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 shadow-none placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="form.errors.email"
                />
            </div>

            <!-- Email verification -->
            <div
                v-if="
                    mustVerifyEmail &&
                    user.email_verified_at === null
                "
                class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3"
            >
                <p class="text-sm text-amber-800">
                    Your email address is unverified.

                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="font-medium underline hover:text-amber-950"
                    >
                        Re-send verification email
                    </Link>
                </p>

                <p
                    v-show="
                        status ===
                        'verification-link-sent'
                    "
                    class="mt-2 text-sm font-medium text-emerald-700"
                >
                    A new verification link has been sent.
                </p>
            </div>

            <!-- Save -->
            <div
                class="flex items-center gap-4 border-t border-slate-100 pt-5"
            >
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{
                        form.processing
                            ? 'Saving...'
                            : 'Save changes'
                    }}
                </button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="form.recentlySuccessful"
                        class="flex items-center gap-1.5 text-sm font-medium text-emerald-600"
                    >
                        <svg
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
                                d="m9 12 2 2 4-4"
                            />
                            <circle
                                cx="12"
                                cy="12"
                                r="9"
                            />
                        </svg>

                        Saved
                    </div>
                </Transition>
            </div>
        </form>
    </section>
</template>