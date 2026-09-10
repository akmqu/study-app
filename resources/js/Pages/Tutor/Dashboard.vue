<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    stats: Object,
});
</script>

<template>
    <Head title="Tutor Dashboard" />

    <div class="p-6 bg-gray-100 min-h-screen">
        <h1 class="text-2xl font-bold mb-6">Tutor Dashboard</h1>
        
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white p-4 rounded shadow">
                <p class="text-gray-500">Active Students</p>
                <p class="text-xl font-bold">{{ stats.activeStudents }}</p>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <p class="text-gray-500">Pending Reviews</p>
                <p class="text-xl font-bold">{{ stats.pendingReviews }}</p>
            </div>
        </div>
    </div>
</template>