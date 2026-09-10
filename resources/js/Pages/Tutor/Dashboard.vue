<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
});
</script>

<template>
    <Head title="Tutor Dashboard" />

    <div class="min-h-screen bg-gray-100 p-6">
            <div class="mb-6 flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-gray-900">Tutor Dashboard</h1>

            <div class="flex items-center gap-2">
                <Link
                    :href="route('tutor.students')"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                >
                    Students
                </Link>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                >
                    Log Out
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="rounded bg-white p-4 shadow">
                <p class="text-gray-500">Active Students</p>
                <p class="text-xl font-bold">{{ stats.activeStudents }}</p>
            </div>
            <div class="rounded bg-white p-4 shadow">
                <p class="text-gray-500">Pending Reviews</p>
                <p class="text-xl font-bold">{{ stats.pendingReviews }}</p>
            </div>
        </div>
    </div>
</template>
