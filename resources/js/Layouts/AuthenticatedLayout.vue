<script setup>
import { computed, ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const page = usePage();

const user = computed(() => page.props.auth.user);

const isTutor = computed(() => user.value?.role === 'tutor');

const roleLabel = computed(() =>
    isTutor.value ? 'Tutor' : 'Student'
);

const homeRoute = computed(() =>
    isTutor.value ? 'tutor.dashboard' : 'student.dashboard'
);

const initials = computed(() => {
    const name = user.value?.name ?? '';

    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
});

const navigation = computed(() => {
    if (isTutor.value) {
        return [
            {
                label: 'Dashboard',
                route: 'tutor.dashboard',
                active: 'tutor.dashboard',
            },
            {
                label: 'Students',
                route: 'tutor.students',
                active: 'tutor.students*',
            },
        ];
    }

    return [
        {
            label: 'Dashboard',
            route: 'student.dashboard',
            active: 'student.dashboard',
        },
        {
            label: 'Assignments',
            route: 'student.assignments',
            active: 'student.assignments*',
        },
    ];
});

const isActive = (pattern) => {
    return route().current(pattern);
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-900">

        <!-- Top navigation -->
        <header
            class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur"
        >
            <div
                class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6"
            >
                <!-- Logo -->
                <div class="flex items-center gap-8">

                    <Link
                        :href="route(homeRoute)"
                        class="flex items-center gap-2"
                    >
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-white shadow-sm"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="h-5 w-5"
                            >
                                <path d="m3 10 9-5 9 5-9 5Z" />
                                <path d="M7 12v5c3 2 7 2 10 0v-5" />
                            </svg>
                        </span>

                        <span
                            class="text-lg font-semibold tracking-tight text-slate-900"
                        >
                            Tutorly
                        </span>
                    </Link>

                    <!-- Desktop navigation -->
                    <nav class="hidden items-center gap-1 md:flex">
                        <Link
                            v-for="item in navigation"
                            :key="item.route"
                            :href="route(item.route)"
                            class="rounded-lg px-3 py-2 text-sm font-medium transition"
                            :class="
                                isActive(item.active)
                                    ? 'bg-indigo-50 text-indigo-700'
                                    : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900'
                            "
                        >
                            {{ item.label }}
                        </Link>
                    </nav>
                </div>

                <!-- User menu -->
                <div class="hidden items-center md:flex">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                type="button"
                                class="flex items-center gap-2 rounded-full border border-slate-200 bg-white py-1 pl-1 pr-3 transition hover:bg-slate-50"
                            >
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-xs font-semibold text-white"
                                >
                                    {{ initials }}
                                </span>

                                <span class="text-left leading-tight">
                                    <span
                                        class="block max-w-32 truncate text-sm font-medium text-slate-800"
                                    >
                                        {{ user.name }}
                                    </span>

                                    <span class="block text-xs text-slate-500">
                                        {{ roleLabel }}
                                    </span>
                                </span>

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    class="h-4 w-4 text-slate-400"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0l-4.25-4.51a.75.75 0 0 1 .02-1.06Z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <div class="border-b border-slate-100 px-4 py-3">
                                <p
                                    class="truncate text-sm font-medium text-slate-900"
                                >
                                    {{ user.name }}
                                </p>

                                <p
                                    class="truncate text-xs text-slate-500"
                                >
                                    {{ user.email }}
                                </p>
                            </div>

                            <DropdownLink
                                :href="route('profile.edit')"
                            >
                                Profile
                            </DropdownLink>

                            <DropdownLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>

                <!-- Mobile button -->
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg p-2 text-slate-500 hover:bg-slate-100 md:hidden"
                    @click="
                        showingNavigationDropdown =
                            !showingNavigationDropdown
                    "
                >
                    <svg
                        v-if="!showingNavigationDropdown"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-6 w-6"
                    >
                        <path
                            stroke-linecap="round"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>

                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-6 w-6"
                    >
                        <path
                            stroke-linecap="round"
                            d="M6 6l12 12M18 6 6 18"
                        />
                    </svg>
                </button>
            </div>

            <!-- Mobile navigation -->
            <div
                v-if="showingNavigationDropdown"
                class="border-t border-slate-200 bg-white px-4 py-4 md:hidden"
            >
                <nav class="space-y-1">
                    <Link
                        v-for="item in navigation"
                        :key="item.route"
                        :href="route(item.route)"
                        class="block rounded-lg px-3 py-2 text-sm font-medium"
                        :class="
                            isActive(item.active)
                                ? 'bg-indigo-50 text-indigo-700'
                                : 'text-slate-600 hover:bg-slate-100'
                        "
                    >
                        {{ item.label }}
                    </Link>
                </nav>

                <div
                    class="mt-4 border-t border-slate-200 pt-4"
                >
                    <div class="px-3">
                        <p class="text-sm font-medium text-slate-900">
                            {{ user.name }}
                        </p>

                        <p class="text-xs text-slate-500">
                            {{ user.email }}
                        </p>
                    </div>

                    <div class="mt-3 space-y-1">
                        <Link
                            :href="route('profile.edit')"
                            class="block rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-slate-100"
                        >
                            Profile
                        </Link>

                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="block w-full rounded-lg px-3 py-2 text-left text-sm text-slate-600 hover:bg-slate-100"
                        >
                            Log Out
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Header slot used by older pages -->
        <header
            v-if="$slots.header"
            class="mx-auto max-w-6xl px-4 pt-8 sm:px-6"
        >
            <slot name="header" />
        </header>

        <!-- Page content -->
        <main>
            <slot />
        </main>
    </div>
</template>