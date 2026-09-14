<script setup>
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => {
        passwordInput.value?.focus();
    });
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,

        onSuccess: () => closeModal(),

        onError: () => {
            passwordInput.value?.focus();
        },

        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section>
        <div
            class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between"
        >
            <div class="flex items-start gap-3">
                <span
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600"
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
                            d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6"
                        />

                        <path
                            stroke-linecap="round"
                            d="M10 11v5M14 11v5"
                        />
                    </svg>
                </span>

                <div>
                    <h2 class="font-medium text-slate-900">
                        Delete account
                    </h2>

                    <p
                        class="mt-1 max-w-xl text-sm leading-6 text-slate-500"
                    >
                        Permanently delete your account and
                        all associated data. This action
                        cannot be undone.
                    </p>
                </div>
            </div>

            <button
                type="button"
                class="shrink-0 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-700"
                @click="confirmUserDeletion"
            >
                Delete account
            </button>
        </div>

        <!-- Confirmation modal -->
        <Modal
            :show="confirmingUserDeletion"
            max-width="md"
            @close="closeModal"
        >
            <div class="p-6">
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-full bg-red-50 text-red-600"
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
                            d="M12 9v4M12 17h.01"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M10.3 3.7 2.2 18a2 2 0 0 0 1.7 3h16.2a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0Z"
                        />
                    </svg>
                </div>

                <h2
                    class="mt-4 text-lg font-semibold text-slate-900"
                >
                    Delete your account?
                </h2>

                <p
                    class="mt-2 text-sm leading-6 text-slate-500"
                >
                    This will permanently delete your
                    account and its data. Enter your
                    password to confirm.
                </p>

                <div class="mt-5">
                    <label
                        for="delete-password"
                        class="mb-1.5 block text-sm font-medium text-slate-700"
                    >
                        Password
                    </label>

                    <input
                        id="delete-password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        placeholder="Enter your password"
                        class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-red-500 focus:bg-white focus:ring-red-500"
                        @keyup.enter="deleteUser"
                    />

                    <InputError
                        class="mt-1.5"
                        :message="form.errors.password"
                    />
                </div>

                <div
                    class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-5"
                >
                    <button
                        type="button"
                        :disabled="form.processing"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50"
                        @click="closeModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        :disabled="form.processing"
                        class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="deleteUser"
                    >
                        {{
                            form.processing
                                ? 'Deleting...'
                                : 'Delete account'
                        }}
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>