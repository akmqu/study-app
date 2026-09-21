<script setup>
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';

import { useForm } from '@inertiajs/vue3';

import {
    nextTick,
    ref,
} from 'vue';

const confirmingUserDeletion =
    ref(false);

const passwordInput =
    ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value =
        true;

    nextTick(() => {
        passwordInput.value
            ?.focus();
    });
};

const deleteUser = () => {
    form.delete(
        route(
            'profile.destroy'
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                closeModal();
            },

            onError: () => {
                passwordInput.value
                    ?.focus();
            },

            onFinish: () => {
                form.reset();
            },
        }
    );
};

const closeModal = () => {
    confirmingUserDeletion.value =
        false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section>
        <div
            class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2
                    class="text-base font-semibold text-slate-900"
                >
                    Delete account
                </h2>

                <p
                    class="mt-1 max-w-xl text-sm leading-6 text-slate-500"
                >
                    Permanently delete your account
                    and all associated data. This
                    action cannot be undone.
                </p>
            </div>

            <button
                type="button"
                class="shrink-0 rounded-md border border-red-300 bg-white px-4 py-2.5 text-sm font-medium text-red-600 transition hover:bg-red-50"
                @click="
                    confirmUserDeletion
                "
            >
                Delete account
            </button>
        </div>

        <Modal
            :show="
                confirmingUserDeletion
            "
            max-width="md"
            @close="
                closeModal
            "
        >
            <div class="p-6">
                <h2
                    class="text-lg font-semibold text-slate-950"
                >
                    Delete your account?
                </h2>

                <p
                    class="mt-2 text-sm leading-6 text-slate-500"
                >
                    This action is permanent.
                    Enter your password to confirm
                    account deletion.
                </p>

                <div class="mt-5">
                    <label
                        for="delete-password"
                        class="block text-sm font-medium text-slate-700"
                    >
                        Password
                    </label>

                    <input
                        id="delete-password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        placeholder="Enter your password"
                        class="mt-2 block w-full rounded-md border-slate-300 bg-white px-3 py-2.5 text-sm placeholder:text-slate-400 focus:border-red-500 focus:ring-red-500"
                        @keyup.enter="
                            deleteUser
                        "
                    />

                    <InputError
                        class="mt-1.5"
                        :message="
                            form.errors.password
                        "
                    />
                </div>

                <div
                    class="mt-6 flex justify-end gap-3 border-t border-slate-200 pt-5"
                >
                    <button
                        type="button"
                        :disabled="
                            form.processing
                        "
                        class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        @click="
                            closeModal
                        "
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        :disabled="
                            form.processing
                        "
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700 disabled:opacity-40"
                        @click="
                            deleteUser
                        "
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