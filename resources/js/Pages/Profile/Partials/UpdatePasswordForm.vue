<script setup>
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,

        onSuccess: () => form.reset(),

        onError: () => {
            if (form.errors.password) {
                form.reset(
                    'password',
                    'password_confirmation'
                );

                passwordInput.value?.focus();
            }

            if (form.errors.current_password) {
                form.reset('current_password');

                currentPasswordInput.value?.focus();
            }
        },
    });
};
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
                </svg>
            </span>

            <div>
                <h2 class="font-medium text-slate-900">
                    Password
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Use a secure password to protect your
                    account.
                </p>
            </div>
        </div>

        <form
            class="mt-6 max-w-xl space-y-5"
            @submit.prevent="updatePassword"
        >
            <!-- Current password -->
            <div>
                <label
                    for="current_password"
                    class="mb-1.5 block text-sm font-medium text-slate-700"
                >
                    Current password
                </label>

                <input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="Enter current password"
                    class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 shadow-none placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="
                        form.errors.current_password
                    "
                />
            </div>

            <!-- New password -->
            <div>
                <label
                    for="password"
                    class="mb-1.5 block text-sm font-medium text-slate-700"
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
                    class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 shadow-none placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
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
                    class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 shadow-none placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="
                        form.errors.password_confirmation
                    "
                />
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
                            ? 'Updating...'
                            : 'Update password'
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

                        Password updated
                    </div>
                </Transition>
            </div>
        </form>
    </section>
</template>