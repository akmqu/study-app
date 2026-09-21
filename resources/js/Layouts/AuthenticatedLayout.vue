<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const user = computed(
    () => page.props.auth?.user ?? {}
);

const isTutor = computed(
    () => user.value?.role === 'tutor'
);

const mobileMenuOpen = ref(false);

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
            {
                label: 'Calendar',
                route: 'tutor.calendar',
                active: 'tutor.calendar*',
            },
            {
                label: 'Assignments',
                route: 'tutor.assignments',
                active: 'tutor.assignments*',
            },
        ];
    }

    return [
        {
            label: 'Home',
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

const initials = computed(() => {
    return String(user.value?.name ?? '')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map(
            (part) =>
                part
                    .charAt(0)
                    .toUpperCase()
        )
        .join('');
});

const roleLabel = computed(() => {
    return isTutor.value
        ? 'Tutor'
        : 'Student';
});
</script>

<template>
    <div
        class="min-h-screen bg-slate-50 text-slate-900"
    >
        <!-- Desktop sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-40 hidden w-60 border-r border-slate-200 bg-white md:flex md:flex-col"
        >
            <!-- Logo -->
            <div
                class="flex h-16 items-center border-b border-slate-200 px-5"
            >
                <Link
                    :href="
                        route(
                            isTutor
                                ? 'tutor.dashboard'
                                : 'student.dashboard'
                        )
                    "
                    class="text-base font-semibold tracking-tight text-slate-950"
                >
                    Tutorly
                </Link>
            </div>

            <!-- Navigation -->
            <nav
                class="flex-1 px-3 py-5"
            >
                <div class="space-y-1">
                    <Link
                        v-for="item in navigation"
                        :key="item.route"
                        :href="
                            route(
                                item.route
                            )
                        "
                        class="block rounded-md px-3 py-2.5 text-sm font-medium transition"
                        :class="
                            isActive(
                                item.active
                            )
                                ? 'bg-slate-100 text-slate-950'
                                : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950'
                        "
                    >
                        {{ item.label }}
                    </Link>
                </div>
            </nav>

            <!-- User -->
            <div
                class="border-t border-slate-200 p-3"
            >
                <Link
                    :href="
                        route(
                            'profile.edit'
                        )
                    "
                    class="mb-2 block rounded-md px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-50 hover:text-slate-950"
                >
                    Profile
                </Link>

                <div
                    class="flex items-center gap-3 px-3 py-2"
                >
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-700"
                    >
                        {{ initials }}
                    </div>

                    <div class="min-w-0">
                        <p
                            class="truncate text-sm font-medium text-slate-900"
                        >
                            {{ user.name }}
                        </p>

                        <p
                            class="text-xs text-slate-500"
                        >
                            {{ roleLabel }}
                        </p>
                    </div>
                </div>

                <Link
                    :href="
                        route(
                            'logout'
                        )
                    "
                    method="post"
                    as="button"
                    class="mt-1 w-full rounded-md px-3 py-2 text-left text-sm text-slate-500 transition hover:bg-slate-50 hover:text-slate-950"
                >
                    Log out
                </Link>
            </div>
        </aside>

        <!-- Mobile header -->
        <header
            class="sticky top-0 z-40 flex h-14 items-center justify-between border-b border-slate-200 bg-white px-4 md:hidden"
        >
            <Link
                :href="
                    route(
                        isTutor
                            ? 'tutor.dashboard'
                            : 'student.dashboard'
                    )
                "
                class="font-semibold tracking-tight"
            >
                Tutorly
            </Link>

            <button
                type="button"
                class="rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-100"
                @click="
                    mobileMenuOpen =
                        !mobileMenuOpen
                "
            >
                {{
                    mobileMenuOpen
                        ? 'Close'
                        : 'Menu'
                }}
            </button>
        </header>

        <!-- Mobile navigation -->
        <div
            v-if="mobileMenuOpen"
            class="border-b border-slate-200 bg-white px-4 py-3 md:hidden"
        >
            <nav class="space-y-1">
                <Link
                    v-for="item in navigation"
                    :key="item.route"
                    :href="
                        route(
                            item.route
                        )
                    "
                    class="block rounded-md px-3 py-2 text-sm font-medium"
                    :class="
                        isActive(
                            item.active
                        )
                            ? 'bg-slate-100 text-slate-950'
                            : 'text-slate-600'
                    "
                    @click="
                        mobileMenuOpen =
                            false
                    "
                >
                    {{ item.label }}
                </Link>

                <div
                    class="my-2 border-t border-slate-200"
                ></div>

                <Link
                    :href="
                        route(
                            'profile.edit'
                        )
                    "
                    class="block rounded-md px-3 py-2 text-sm text-slate-600"
                >
                    Profile
                </Link>

                <Link
                    :href="
                        route(
                            'logout'
                        )
                    "
                    method="post"
                    as="button"
                    class="block w-full rounded-md px-3 py-2 text-left text-sm text-slate-600"
                >
                    Log out
                </Link>
            </nav>
        </div>

        <!-- Page content -->
        <div class="md:pl-60">
            <main
                class="mx-auto min-h-screen w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
            >
                <slot />
            </main>
        </div>
    </div>
</template>