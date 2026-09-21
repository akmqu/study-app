<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import {
    Head,
    Link,
} from '@inertiajs/vue3';

const props = defineProps({
    students: {
        type: Array,
        default: () => [],
    },

    upcomingLessons: {
        type: Array,
        default: () => [],
    },

    pendingReviews: {
        type: Array,
        default: () => [],
    },
});

const formatLessonTime = (
    value
) => {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat(
        undefined,
        {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }
    ).format(
        new Date(value)
    );
};

const formatSubmittedTime = (
    value
) => {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat(
        undefined,
        {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }
    ).format(
        new Date(value)
    );
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div
            class="mx-auto max-w-5xl"
        >
            <!-- Heading -->
            <header
                class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-950"
                    >
                        Dashboard
                    </h1>

                    <p
                        class="mt-2 text-sm text-slate-500"
                    >
                        Your upcoming work
                        and recent activity.
                    </p>
                </div>

                <Link
                    :href="
                        route(
                            'tutor.assignments'
                        )
                    "
                    class="inline-flex items-center justify-center rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800"
                >
                    + New assignment
                </Link>
            </header>

            <!-- Upcoming lessons -->
            <section
                class="mt-8"
            >
                <div
                    class="flex items-center justify-between gap-4"
                >
                    <div>
                        <h2
                            class="text-base font-semibold text-slate-900"
                        >
                            Upcoming lessons
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Your next scheduled
                            lessons.
                        </p>
                    </div>

                    <Link
                        :href="
                            route(
                                'tutor.calendar'
                            )
                        "
                        class="text-sm font-medium text-slate-600 hover:text-slate-950"
                    >
                        View calendar →
                    </Link>
                </div>

                <div
                    v-if="
                        upcomingLessons.length
                    "
                    class="mt-5 border-t border-slate-200"
                >
                    <div
                        v-for="lesson in upcomingLessons"
                        :key="
                            lesson.id
                        "
                        class="grid gap-2 border-b border-slate-200 py-4 sm:grid-cols-[180px_minmax(0,1fr)_180px] sm:items-center"
                    >
                        <p
                            class="text-sm font-medium text-slate-900"
                        >
                            {{
                                formatLessonTime(
                                    lesson.start_time
                                )
                            }}
                        </p>

                        <div
                            class="min-w-0"
                        >
                            <p
                                class="truncate text-sm font-medium text-slate-900"
                            >
                                {{
                                    lesson.student_name
                                }}
                            </p>

                            <p
                                class="mt-0.5 text-sm text-slate-500"
                            >
                                {{
                                    lesson.subject
                                }}
                            </p>
                        </div>

                        <p
                            class="text-sm text-slate-500 sm:text-right"
                        >
                            Scheduled
                        </p>
                    </div>
                </div>

                <div
                    v-else
                    class="mt-5 border-t border-slate-200 py-8"
                >
                    <p
                        class="text-sm text-slate-500"
                    >
                        No upcoming lessons.
                    </p>
                </div>
            </section>

            <!-- Reviews -->
            <section
                class="mt-10"
            >
                <div
                    class="flex items-center justify-between gap-4"
                >
                    <div>
                        <h2
                            class="text-base font-semibold text-slate-900"
                        >
                            Waiting for review
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Submitted homework
                            that needs your attention.
                        </p>
                    </div>

                    <Link
                        :href="
                            route(
                                'tutor.assignments'
                            )
                        "
                        class="text-sm font-medium text-slate-600 hover:text-slate-950"
                    >
                        View assignments →
                    </Link>
                </div>

                <div
                    v-if="
                        pendingReviews.length
                    "
                    class="mt-5 border-t border-slate-200"
                >
                    <div
                        v-for="assignment in pendingReviews"
                        :key="
                            assignment.id
                        "
                        class="grid gap-2 border-b border-slate-200 py-4 sm:grid-cols-[minmax(0,1fr)_180px_170px] sm:items-center"
                    >
                        <div
                            class="min-w-0"
                        >
                            <p
                                class="truncate text-sm font-medium text-slate-900"
                            >
                                {{
                                    assignment.title
                                }}
                            </p>

                            <p
                                class="mt-0.5 text-sm text-slate-500"
                            >
                                {{
                                    assignment.student_name
                                }}

                                <span
                                    v-if="
                                        assignment.subject
                                    "
                                    class="mx-1 text-slate-300"
                                >
                                    ·
                                </span>

                                <span
                                    v-if="
                                        assignment.subject
                                    "
                                >
                                    {{
                                        assignment.subject
                                    }}
                                </span>
                            </p>
                        </div>

                        <p
                            class="text-sm text-slate-500"
                        >
                            {{
                                formatSubmittedTime(
                                    assignment.submitted_at
                                )
                            }}
                        </p>

                        <div
                            class="sm:text-right"
                        >
                            <Link
                                :href="
                                    route(
                                        'tutor.assignments'
                                    )
                                "
                                class="text-sm font-medium text-slate-700 hover:text-slate-950"
                            >
                                Review →
                            </Link>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="mt-5 border-t border-slate-200 py-8"
                >
                    <p
                        class="text-sm text-slate-500"
                    >
                        Nothing waiting for review.
                    </p>
                </div>
            </section>

            <!-- Students -->
            <section
                class="mt-10"
            >
                <div
                    class="flex items-center justify-between gap-4"
                >
                    <div>
                        <h2
                            class="text-base font-semibold text-slate-900"
                        >
                            Students
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Recently connected
                            students.
                        </p>
                    </div>

                    <Link
                        :href="
                            route(
                                'tutor.students'
                            )
                        "
                        class="text-sm font-medium text-slate-600 hover:text-slate-950"
                    >
                        View all students →
                    </Link>
                </div>

                <div
                    v-if="
                        students.length
                    "
                    class="mt-5 border-t border-slate-200"
                >
                    <div
                        v-for="student in students"
                        :key="
                            student.id
                        "
                        class="grid gap-2 border-b border-slate-200 py-4 sm:grid-cols-[minmax(0,1fr)_260px] sm:items-center"
                    >
                        <div
                            class="min-w-0"
                        >
                            <p
                                class="truncate text-sm font-medium text-slate-900"
                            >
                                {{
                                    student.name
                                }}
                            </p>

                            <p
                                class="mt-0.5 truncate text-sm text-slate-500"
                            >
                                {{
                                    student.email
                                }}
                            </p>
                        </div>

                        <p
                            class="text-sm text-slate-500 sm:text-right"
                        >
                            {{
                                student.subjects
                                    ?.join(', ')
                                || 'No subject'
                            }}
                        </p>
                    </div>
                </div>

                <div
                    v-else
                    class="mt-5 border-t border-slate-200 py-8"
                >
                    <p
                        class="text-sm text-slate-500"
                    >
                        No students connected yet.
                    </p>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>