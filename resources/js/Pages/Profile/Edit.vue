<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },

    status: {
        type: String,
    },
});

const page = usePage();

const user = computed(() => page.props.auth?.user ?? {});

const initials = computed(() => {
    return String(user.value.name ?? '')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
});

const roleLabel = computed(() => {
    return user.value.role === 'tutor'
        ? 'Tutor account'
        : 'Student account';
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <div
            class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6"
        >
            <!-- Heading -->
            <div class="mb-8">
                <p class="text-sm font-medium text-indigo-600">
                    Account
                </p>

                <h1
                    class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 md:text-3xl"
                >
                    Profile settings
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Manage your personal information, password
                    and account settings.
                </p>
            </div>

            <!-- Profile summary -->
            <section
                class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="flex flex-col gap-4 bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-6 text-white sm:flex-row sm:items-center"
                >
                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full border-4 border-white/20 bg-white/15 text-lg font-semibold"
                    >
                        {{ initials }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <h2
                            class="truncate text-xl font-semibold"
                        >
                            {{ user.name }}
                        </h2>

                        <p
                            class="mt-1 truncate text-sm text-indigo-100"
                        >
                            {{ user.email }}
                        </p>
                    </div>

                    <span
                        class="w-fit rounded-full bg-white/15 px-3 py-1.5 text-xs font-medium text-white"
                    >
                        {{ roleLabel }}
                    </span>
                </div>
            </section>

            <!-- Settings grid -->
            <div class="space-y-6">
                <!-- Profile information -->
                <section
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-2xl"
                    />
                </section>

                <!-- Password -->
                <section
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
                >
                    <UpdatePasswordForm
                        class="max-w-2xl"
                    />
                </section>

                <!-- Danger zone -->
                <section
                    class="rounded-xl border border-red-200 bg-white p-5 shadow-sm sm:p-6"
                >
                    <DeleteUserForm
                        class="max-w-2xl"
                    />
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>