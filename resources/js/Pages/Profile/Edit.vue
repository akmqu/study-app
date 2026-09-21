<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

import {
    Head,
    usePage,
} from '@inertiajs/vue3';

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

const user = computed(
    () => page.props.auth?.user ?? {}
);

const initials = computed(() => {
    return String(
        user.value.name ?? ''
    )
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map(
            (part) =>
                part
                    .charAt(0)
                    .toUpperCase()
        )
        .join('');
});

const roleLabel = computed(() => {
    return user.value.role === 'tutor'
        ? 'Tutor'
        : 'Student';
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <div
            class="mx-auto max-w-6xl"
        >
            <!-- Header -->
            <header>
                <h1
                    class="text-2xl font-semibold tracking-tight text-slate-950"
                >
                    Profile
                </h1>

                <p
                    class="mt-1.5 text-sm text-slate-500"
                >
                    Manage your personal information and account security.
                </p>
            </header>

            <!-- User summary -->
            <section
                class="mt-8 rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center"
                >
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-700"
                    >
                        {{ initials }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <p
                            class="truncate text-base font-semibold text-slate-900"
                        >
                            {{ user.name }}
                        </p>

                        <p
                            class="mt-0.5 truncate text-sm text-slate-500"
                        >
                            {{ user.email }}
                        </p>
                    </div>

                    <span
                        class="w-fit rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600"
                    >
                        {{ roleLabel }}
                    </span>
                </div>
            </section>

            <!-- Settings -->
            <div
                class="mt-6 space-y-6"
            >
                <section
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </section>

                <section
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
                >
                    <UpdatePasswordForm />
                </section>

                <section
                    class="rounded-xl border border-red-200 bg-white p-5 shadow-sm sm:p-6"
                >
                    <DeleteUserForm />
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>