<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    upcomingAssignments: Number,
    paymentStatus: String,
    pendingReviews: Number,
});
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
