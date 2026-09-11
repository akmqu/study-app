<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    stats: Object,
});
</script>

<template>
    <Head title="Tutor Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Tutor Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded bg-white p-4 shadow">
                        <p class="text-gray-500">Active Students</p>
                        <p class="text-xl font-bold">
                            {{ stats.activeStudents }}
                        </p>
                    </div>

                    <div class="rounded bg-white p-4 shadow">
                        <p class="text-gray-500">Pending Reviews</p>
                        <p class="text-xl font-bold">
                            {{ stats.pendingReviews }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>