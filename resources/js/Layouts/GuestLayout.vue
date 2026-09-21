<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="min-h-screen bg-slate-50 text-slate-900"
    >
        <!-- Top bar -->
        <header
            class="border-b border-slate-200 bg-white"
        >
            <div
                class="mx-auto flex h-16 max-w-7xl items-center px-4 sm:px-6 lg:px-8"
            >
                <Link
                    href="/"
                    class="text-base font-semibold tracking-tight text-slate-950"
                >
                    Tutorly
                </Link>
            </div>
        </header>

        <!-- Content -->
        <main
            class="flex min-h-[calc(100vh-65px)] items-center justify-center px-4 py-10 sm:px-6"
        >
            <div class="w-full max-w-md">
                <div
                    class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
                >
                    <slot />
                </div>

                <p
                    class="mt-6 text-center text-xs text-slate-400"
                >
                    Tutorly · Tutor and student workspace
                </p>
            </div>
        </main>
    </div>
</template>