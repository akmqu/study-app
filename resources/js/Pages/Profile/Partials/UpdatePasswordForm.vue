<script setup>
import InputError from '@/Components/InputError.vue';

import { useForm } from '@inertiajs/vue3';

import { ref } from 'vue';

const passwordInput = ref(null);

const currentPasswordInput =
    ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(
        route(
            'password.update'
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                form.reset();
            },

            onError: () => {
                if (
                    form.errors.password
                ) {
                    form.reset(
                        'password',
                        'password_confirmation'
                    );

                    passwordInput.value
                        ?.focus();
                }

                if (
                    form.errors
                        .current_password
                ) {
                    form.reset(
                        'current_password'
                    );

                    currentPasswordInput
                        .value
                        ?.focus();
                }
            },
        }
    );
};
</script>

<template>
    <section>
        <div>
            <h2
                class="text-base font-semibold text-slate-900"
            >
                Password
            </h2>

            <p
                class="mt-1 text-sm text-slate-500"
            >
                Change the password used to access your account.
            </p>
        </div>

        <form
            class="mt-6 max-w-xl space-y-5"
            @submit.prevent="
                updatePassword
            "
        >
            <div>
                <label
                    for="current_password"
                    class="block text-sm font-medium text-slate-700"
                >
                    Current password
                </label>

                <input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="
                        form.current_password
                    "
                    type="password"
                    autocomplete="current-password"
                    placeholder="Enter current password"
                    class="mt-2 block w-full rounded-md border-slate-300 bg-white px-3 py-2.5 text-sm placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="
                        form.errors
                            .current_password
                    "
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
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
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
                    Confirm new password
                </label>

                <input
                    id="password_confirmation"
                    v-model="
                        form.password_confirmation
                    "
                    type="password"
                    autocomplete="new-password"
                    placeholder="Repeat new password"
                    class="mt-2 block w-full rounded-md border-slate-300 bg-white px-3 py-2.5 text-sm placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="
                        form.errors
                            .password_confirmation
                    "
                />
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
                            ? 'Updating...'
                            : 'Update password'
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
                        Password updated
                    </span>
                </Transition>
            </div>
        </form>
    </section>
</template>