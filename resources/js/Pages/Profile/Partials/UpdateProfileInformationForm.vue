<script setup>
import InputError from '@/Components/InputError.vue';

import {
    Link,
    useForm,
    usePage,
} from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },

    status: {
        type: String,
    },
});

const user =
    usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <div>
            <h2
                class="text-base font-semibold text-slate-900"
            >
                Profile information
            </h2>

            <p
                class="mt-1 text-sm text-slate-500"
            >
                Update your name and email address.
            </p>
        </div>

        <form
            class="mt-6 max-w-xl space-y-5"
            @submit.prevent="
                form.patch(
                    route(
                        'profile.update'
                    )
                )
            "
        >
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
                    class="mt-2 block w-full rounded-md border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-slate-500 focus:ring-slate-500"
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
                    class="mt-2 block w-full rounded-md border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-slate-500 focus:ring-slate-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="form.errors.email"
                />
            </div>

            <!-- Verification -->
            <div
                v-if="
                    mustVerifyEmail
                    &&
                    user.email_verified_at ===
                        null
                "
                class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3"
            >
                <p
                    class="text-sm text-amber-800"
                >
                    Your email address is not verified.

                    <Link
                        :href="
                            route(
                                'verification.send'
                            )
                        "
                        method="post"
                        as="button"
                        class="font-medium underline underline-offset-2"
                    >
                        Send verification email
                    </Link>
                </p>

                <p
                    v-if="
                        status ===
                        'verification-link-sent'
                    "
                    class="mt-2 text-sm font-medium text-emerald-700"
                >
                    A new verification link has been sent.
                </p>
            </div>

            <div
                class="flex items-center gap-4 border-t border-slate-200 pt-5"
            >
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800 disabled:opacity-40"
                >
                    {{
                        form.processing
                            ? 'Saving...'
                            : 'Save changes'
                    }}
                </button>

                <Transition
                    enter-active-class="transition"
                    enter-from-class="opacity-0"
                    leave-active-class="transition"
                    leave-to-class="opacity-0"
                >
                    <span
                        v-if="
                            form.recentlySuccessful
                        "
                        class="text-sm font-medium text-emerald-600"
                    >
                        Saved
                    </span>
                </Transition>
            </div>
        </form>
    </section>
</template>