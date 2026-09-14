<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <div class="grid min-h-screen lg:grid-cols-2">

            <!-- Left branding -->
            <div
                class="relative hidden overflow-hidden bg-gradient-to-br from-indigo-600 via-indigo-600 to-violet-700 lg:flex lg:flex-col lg:justify-between"
            >
                <!-- Decorative circles -->
                <div
                    class="absolute -right-24 -top-24 h-80 w-80 rounded-full bg-white/10"
                ></div>

                <div
                    class="absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-white/10"
                ></div>

                <!-- Logo -->
                <div class="relative z-10 p-10">
                    <Link
                        href="/"
                        class="inline-flex items-center gap-3"
                    >
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-indigo-600 shadow-sm"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m3 10 9-5 9 5-9 5Z"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M7 12v5c3 2 7 2 10 0v-5"
                                />
                            </svg>
                        </span>

                        <span
                            class="text-xl font-semibold tracking-tight text-white"
                        >
                            Tutorly
                        </span>
                    </Link>
                </div>

                <!-- Main text -->
                <div
                    class="relative z-10 max-w-xl px-10 pb-12"
                >
                    <p
                        class="text-sm font-medium uppercase tracking-widest text-indigo-200"
                    >
                        Learning made simple
                    </p>

                    <h1
                        class="mt-4 text-4xl font-semibold leading-tight tracking-tight text-white xl:text-5xl"
                    >
                        One place for tutors and students
                        to learn together.
                    </h1>

                    <p
                        class="mt-5 max-w-lg text-base leading-7 text-indigo-100"
                    >
                        Assign homework, track progress,
                        review submissions and keep everything
                        organized in one workspace.
                    </p>

                    <div
                        class="mt-8 grid max-w-lg grid-cols-3 gap-4"
                    >
                        <div
                            class="rounded-xl border border-white/10 bg-white/10 p-4 backdrop-blur"
                        >
                            <p
                                class="text-2xl font-semibold text-white"
                            >
                                01
                            </p>

                            <p
                                class="mt-1 text-xs text-indigo-100"
                            >
                                Manage students
                            </p>
                        </div>

                        <div
                            class="rounded-xl border border-white/10 bg-white/10 p-4 backdrop-blur"
                        >
                            <p
                                class="text-2xl font-semibold text-white"
                            >
                                02
                            </p>

                            <p
                                class="mt-1 text-xs text-indigo-100"
                            >
                                Assign homework
                            </p>
                        </div>

                        <div
                            class="rounded-xl border border-white/10 bg-white/10 p-4 backdrop-blur"
                        >
                            <p
                                class="text-2xl font-semibold text-white"
                            >
                                03
                            </p>

                            <p
                                class="mt-1 text-xs text-indigo-100"
                            >
                                Track progress
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right side -->
            <div
                class="flex min-h-screen flex-col bg-slate-50"
            >
                <!-- Mobile logo -->
                <div class="p-5 lg:hidden">
                    <Link
                        href="/"
                        class="inline-flex items-center gap-2"
                    >
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-white"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m3 10 9-5 9 5-9 5Z"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M7 12v5c3 2 7 2 10 0v-5"
                                />
                            </svg>
                        </span>

                        <span
                            class="text-lg font-semibold text-slate-900"
                        >
                            Tutorly
                        </span>
                    </Link>
                </div>

                <!-- Form -->
                <div
                    class="flex flex-1 items-center justify-center px-4 py-10 sm:px-8"
                >
                    <div class="w-full max-w-md">
                        <slot />
                    </div>
                </div>

                <div
                    class="px-6 pb-6 text-center text-xs text-slate-400"
                >
                    Tutorly · Learning workspace
                </div>
            </div>
        </div>
    </div>
</template>