<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Welcome" />

    <div class="relative min-h-screen overflow-hidden bg-slate-950 text-slate-100">
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,_rgba(56,189,248,0.18),_transparent_55%),radial-gradient(ellipse_at_bottom_right,_rgba(52,211,153,0.12),_transparent_45%)]"
        />

        <div class="relative mx-auto flex min-h-screen w-full max-w-5xl flex-col px-6 py-8">
            <header class="flex items-center justify-between gap-4">
                <div class="text-lg font-semibold tracking-tight text-white">
                    Study App
                </div>

                <nav v-if="canLogin" class="flex items-center gap-2 sm:gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-lg bg-cyan-500 px-4 py-2 text-sm font-medium text-slate-950 transition hover:bg-cyan-400"
                    >
                        Go to Dashboard
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-slate-200 transition hover:bg-white/10 hover:text-white"
                        >
                            Log in
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-lg bg-cyan-500 px-4 py-2 text-sm font-medium text-slate-950 transition hover:bg-cyan-400"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </header>

            <main class="flex flex-1 flex-col items-start justify-center py-16">
                <p class="mb-3 text-sm font-medium uppercase tracking-[0.2em] text-cyan-300/80">
                    Tutoring platform
                </p>
                <h1 class="max-w-2xl text-4xl font-semibold tracking-tight text-white sm:text-5xl">
                    Learn and teach in one place
                </h1>
                <p class="mt-4 max-w-xl text-base leading-relaxed text-slate-300 sm:text-lg">
                    Students track assignments and payments. Tutors manage learners,
                    lessons, and reviews — all from a simple shared account system.
                </p>

                <div
                    v-if="!$page.props.auth.user"
                    class="mt-8 flex flex-wrap items-center gap-3"
                >
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-lg bg-cyan-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-cyan-400"
                    >
                        Create an account
                    </Link>
                    <Link
                        v-if="canLogin"
                        :href="route('login')"
                        class="rounded-lg border border-white/20 bg-white/5 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                    >
                        Log in
                    </Link>
                </div>

                <div v-else class="mt-8">
                    <Link
                        :href="route('dashboard')"
                        class="rounded-lg bg-cyan-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-cyan-400"
                    >
                        Continue to your dashboard
                    </Link>
                </div>
            </main>

            <footer class="pb-4 text-sm text-slate-500">
                Laravel v{{ laravelVersion }} · PHP v{{ phpVersion }}
            </footer>
        </div>
    </div>
</template>
