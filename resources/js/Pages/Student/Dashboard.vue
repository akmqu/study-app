<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    upcomingAssignments: Number,
    paymentStatus: String,
    pendingReviews: Number,
    tutors: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success ?? null);

const form = useForm({
    code: '',
});

const redeem = () => {
    form.code = String(form.code ?? '').trim().toUpperCase();

    form.post(route('student.invitations.redeem'), {
        preserveScroll: true,
        onSuccess: () => form.reset('code'),
    });
};
</script>

<template>
    <Head title="Student Dashboard" />

    <div class="mx-auto min-h-screen max-w-7xl bg-gray-50 p-6">
        <div class="mb-6 flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-gray-900">Student Dashboard</h1>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
            >
                Log Out
            </Link>
        </div>

        <div
            v-if="successMessage"
            class="mb-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
        >
            {{ successMessage }}
        </div>

        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded bg-white p-4 shadow">
                <p class="text-sm text-gray-500">Upcoming Assignments</p>
                <p class="text-2xl font-bold">{{ upcomingAssignments }}</p>
            </div>
            <div class="rounded bg-white p-4 shadow">
                <p class="text-sm text-gray-500">Payment Status</p>
                <p class="text-2xl font-bold text-amber-600">{{ paymentStatus }}</p>
            </div>
            <div class="rounded bg-white p-4 shadow">
                <p class="text-sm text-gray-500">Pending Reviews</p>
                <p class="text-2xl font-bold">{{ pendingReviews ?? 0 }}</p>
            </div>
        </div>

        <div class="mb-6 rounded bg-white p-4 shadow">
            <h2 class="mb-1 text-lg font-semibold text-gray-900">
                Redeem invitation code
            </h2>
            <p class="mb-4 text-sm text-gray-500">
                Enter the 8-character code from your tutor to link your account.
            </p>

            <form
                class="flex flex-col gap-3 sm:flex-row sm:items-start"
                @submit.prevent="redeem"
            >
                <div class="w-full sm:max-w-xs">
                    <label
                        for="code"
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Invitation code
                    </label>
                    <input
                        id="code"
                        v-model="form.code"
                        type="text"
                        maxlength="8"
                        required
                        placeholder="ABCD1234"
                        class="block w-full rounded-md border-gray-300 font-mono uppercase tracking-wider shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <p
                        v-if="form.errors.code"
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ form.errors.code }}
                    </p>
                </div>

                <button
                    type="submit"
                    class="mt-6 inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-gray-800 disabled:opacity-50 sm:mt-7"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Linking…' : 'Link Tutor' }}
                </button>
            </form>
        </div>

        <div class="mb-6 overflow-hidden rounded bg-white shadow">
            <div class="border-b border-gray-100 px-4 py-3">
                <h2 class="text-lg font-semibold text-gray-900">Your tutors</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-600">
                                Name
                            </th>
                            <th class="px-4 py-3 font-medium text-gray-600">
                                Email
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="tutors.length === 0">
                            <td
                                colspan="2"
                                class="px-4 py-8 text-center text-gray-500"
                            >
                                No tutors linked yet. Redeem an invitation code
                                above.
                            </td>
                        </tr>
                        <tr
                            v-for="tutor in tutors"
                            :key="tutor.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ tutor.name }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ tutor.email }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded bg-white p-4 shadow">
            <h2 class="mb-2 text-lg font-semibold">Quick Notes</h2>
            <textarea
                class="w-full rounded-md border-gray-300 p-2 text-sm"
                rows="3"
                placeholder="Write quick reminders here..."
            ></textarea>
        </div>
    </div>
</template>
