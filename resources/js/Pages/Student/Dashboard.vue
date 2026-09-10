<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    upcomingAssignments: Number,
    paymentStatus: String,
    pendingReviews: Number,
});
</script>

<template>
    <Head title="Student Dashboard" />

    <div class="p-6 bg-gray-50 min-h-screen max-w-7xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Student Dashboard</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-4 rounded shadow">
                <p class="text-gray-500 text-sm">Upcoming Assignments</p>
                <p class="text-2xl font-bold">{{ upcomingAssignments }}</p>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <p class="text-gray-500 text-sm">Payment Status</p>
                <p class="text-2xl font-bold text-amber-600">{{ paymentStatus }}</p>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <p class="text-gray-500 text-sm">Pending Reviews</p>
                <p class="text-2xl font-bold">{{ pendingReviews ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <h2 class="text-lg font-semibold mb-2">Quick Notes</h2>
            <textarea class="w-full border-gray-300 rounded-md p-2 text-sm" rows="3" placeholder="Write quick reminders here..."></textarea>
        </div>
    </div>
</template>