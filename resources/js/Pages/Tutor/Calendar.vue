<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';

import {
    Head,
    router,
    useForm,
    usePage,
} from '@inertiajs/vue3';

import {
    computed,
    ref,
    watch,
} from 'vue';

import FullCalendar from '@fullcalendar/vue3';
import classicThemePlugin from '@fullcalendar/vue3/themes/classic';
import dayGridPlugin from '@fullcalendar/vue3/daygrid';
import timeGridPlugin from '@fullcalendar/vue3/timegrid';
import interactionPlugin from '@fullcalendar/vue3/interaction';

import '@fullcalendar/vue3/skeleton.css';
import '@fullcalendar/vue3/themes/classic/theme.css';
import '@fullcalendar/vue3/themes/classic/palette.css';

const props = defineProps({
    lessons: {
        type: Array,
        default: () => [],
    },

    students: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

/*
|--------------------------------------------------------------------------
| Flash
|--------------------------------------------------------------------------
*/

const successMessage = computed(
    () => page.props.flash?.success ?? null
);

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const pad = (value) => {
    return String(value).padStart(2, '0');
};

const formatDateInput = (date) => {
    return [
        date.getFullYear(),
        pad(date.getMonth() + 1),
        pad(date.getDate()),
    ].join('-');
};

const formatTimeInput = (date) => {
    return `${pad(date.getHours())}:${pad(
        date.getMinutes()
    )}`;
};

const formatLessonDate = (value) => {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat('en', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(value));
};

const formatLessonTime = (value) => {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat('en', {
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
};

/*
|--------------------------------------------------------------------------
| Add lesson
|--------------------------------------------------------------------------
*/

const showLessonModal = ref(false);

const lessonForm = useForm({
    student_id: '',
    subject: '',
    date: '',
    start_time: '',
    end_time: '',
});

const selectedStudent = computed(() => {
    return props.students.find(
        (student) =>
            String(student.id) ===
            String(lessonForm.student_id)
    );
});

const availableSubjects = computed(() => {
    return selectedStudent.value?.subjects ?? [];
});

watch(
    () => lessonForm.student_id,
    () => {
        lessonForm.subject = '';

        if (availableSubjects.value.length === 1) {
            lessonForm.subject =
                availableSubjects.value[0];
        }
    }
);

const openLessonModal = (
    startDate = null,
    endDate = null
) => {
    let start = startDate
        ? new Date(startDate)
        : new Date();

    if (!startDate) {
        start.setSeconds(0, 0);
        start.setMinutes(0);
        start.setHours(start.getHours() + 1);
    }

    const end = endDate
        ? new Date(endDate)
        : new Date(
            start.getTime() + 60 * 60 * 1000
        );

    lessonForm.clearErrors();

    lessonForm.student_id = '';
    lessonForm.subject = '';
    lessonForm.date =
        formatDateInput(start);
    lessonForm.start_time =
        formatTimeInput(start);
    lessonForm.end_time =
        formatTimeInput(end);

    showLessonModal.value = true;
};

const closeLessonModal = () => {
    if (lessonForm.processing) {
        return;
    }

    showLessonModal.value = false;

    lessonForm.reset();
    lessonForm.clearErrors();
};

const submitLesson = () => {
    if (lessonForm.processing) {
        return;
    }

    lessonForm.post(
        route('tutor.lessons.store'),
        {
            preserveScroll: true,

            onSuccess: () => {
                showLessonModal.value = false;

                lessonForm.reset();
                lessonForm.clearErrors();
            },
        }
    );
};

/*
|--------------------------------------------------------------------------
| Calendar slots
|--------------------------------------------------------------------------
*/

const handleDateClick = (info) => {
    const start = new Date(info.date);

    if (info.allDay) {
        start.setHours(9, 0, 0, 0);
    }

    const end = new Date(
        start.getTime() + 60 * 60 * 1000
    );

    openLessonModal(start, end);
};

const handleSelect = (info) => {
    openLessonModal(
        info.start,
        info.end
    );
};

/*
|--------------------------------------------------------------------------
| Lesson details
|--------------------------------------------------------------------------
*/

const showLessonDetailsModal = ref(false);
const selectedLesson = ref(null);

const lessonActionProcessing = ref(false);

const openLessonDetails = (info) => {
    const lesson = props.lessons.find(
        (item) =>
            String(item.id) ===
            String(info.event.id)
    );

    if (!lesson) {
        return;
    }

    selectedLesson.value = lesson;
    showLessonDetailsModal.value = true;
};

const closeLessonDetails = () => {
    if (lessonActionProcessing.value) {
        return;
    }

    showLessonDetailsModal.value = false;
    selectedLesson.value = null;
};

/*
|--------------------------------------------------------------------------
| Complete
|--------------------------------------------------------------------------
*/

const markLessonCompleted = () => {
    if (
        !selectedLesson.value ||
        lessonActionProcessing.value
    ) {
        return;
    }

    lessonActionProcessing.value = true;

    router.patch(
        route(
            'tutor.lessons.complete',
            selectedLesson.value.id
        ),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                showLessonDetailsModal.value = false;
                selectedLesson.value = null;
            },

            onFinish: () => {
                lessonActionProcessing.value = false;
            },
        }
    );
};

/*
|--------------------------------------------------------------------------
| Cancel
|--------------------------------------------------------------------------
*/

const cancelLesson = () => {
    if (
        !selectedLesson.value ||
        lessonActionProcessing.value
    ) {
        return;
    }

    lessonActionProcessing.value = true;

    router.patch(
        route(
            'tutor.lessons.cancel',
            selectedLesson.value.id
        ),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                showLessonDetailsModal.value = false;
                selectedLesson.value = null;
            },

            onFinish: () => {
                lessonActionProcessing.value = false;
            },
        }
    );
};

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

const deleteLesson = () => {
    if (
        !selectedLesson.value ||
        lessonActionProcessing.value
    ) {
        return;
    }

    const confirmed = window.confirm(
        'Delete this lesson permanently?'
    );

    if (!confirmed) {
        return;
    }

    lessonActionProcessing.value = true;

    router.delete(
        route(
            'tutor.lessons.destroy',
            selectedLesson.value.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                showLessonDetailsModal.value = false;
                selectedLesson.value = null;
            },

            onFinish: () => {
                lessonActionProcessing.value = false;
            },
        }
    );
};

/*
|--------------------------------------------------------------------------
| Calendar events
|--------------------------------------------------------------------------
*/

const calendarEvents = computed(() => {
    return props.lessons.map((lesson) => {
        return {
            id: String(lesson.id),

            title: lesson.subject
                ? `${lesson.student_name} · ${lesson.subject}`
                : lesson.student_name,

            start: lesson.start_time,
            end: lesson.end_time,

            classNames: [
                `lesson-${lesson.status}`,
            ],

            extendedProps: {
                studentId:
                    lesson.student_id,

                studentName:
                    lesson.student_name,

                subject:
                    lesson.subject,

                status:
                    lesson.status,
            },
        };
    });
});

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

const todayCount = computed(() => {
    const today =
        formatDateInput(new Date());

    return props.lessons.filter((lesson) => {
        if (
            !lesson.start_time ||
            lesson.status === 'cancelled'
        ) {
            return false;
        }

        return (
            formatDateInput(
                new Date(lesson.start_time)
            ) === today
        );
    }).length;
});

const weekCount = computed(() => {
    const now = new Date();

    const start = new Date(now);

    const day =
        start.getDay() === 0
            ? 7
            : start.getDay();

    start.setDate(
        start.getDate() - day + 1
    );

    start.setHours(0, 0, 0, 0);

    const end = new Date(start);

    end.setDate(
        end.getDate() + 7
    );

    return props.lessons.filter((lesson) => {
        if (
            !lesson.start_time ||
            lesson.status === 'cancelled'
        ) {
            return false;
        }

        const lessonDate =
            new Date(lesson.start_time);

        return (
            lessonDate >= start &&
            lessonDate < end
        );
    }).length;
});

const completedCount = computed(() => {
    const now = new Date();

    return props.lessons.filter((lesson) => {
        if (
            !lesson.start_time ||
            lesson.status !== 'completed'
        ) {
            return false;
        }

        const lessonDate =
            new Date(lesson.start_time);

        return (
            lessonDate.getFullYear() ===
                now.getFullYear() &&
            lessonDate.getMonth() ===
                now.getMonth()
        );
    }).length;
});

/*
|--------------------------------------------------------------------------
| FullCalendar
|--------------------------------------------------------------------------
*/

const calendarOptions = computed(() => ({
    plugins: [
        classicThemePlugin,
        dayGridPlugin,
        timeGridPlugin,
        interactionPlugin,
    ],

    initialView: 'timeGridWeek',

    headerToolbar: {
        start: 'prev,next today',
        center: 'title',
        end: 'dayGridMonth,timeGridWeek',
    },

    buttons: {
        today: {
            text: 'Today',
        },

        dayGridMonth: {
            text: 'Month',
        },

        timeGridWeek: {
            text: 'Week',
        },
    },

    firstDay: 1,

    height: 'auto',
    expandRows: true,

    nowIndicator: true,

    selectable: true,
    selectMirror: true,

    allDaySlot: false,

    slotMinTime: '07:00:00',
    slotMaxTime: '22:00:00',

    slotDuration: '00:30:00',
    slotLabelInterval: '01:00:00',

    weekends: true,

    dateClick: handleDateClick,
    select: handleSelect,
    eventClick: openLessonDetails,

    events: calendarEvents.value,

    toolbarClass:
        'tutorly-calendar-toolbar',

    toolbarTitleClass:
        'tutorly-calendar-title',

    toolbarSectionClass:
        'tutorly-calendar-section',

    buttonClass:
        'tutorly-calendar-button',
}));
</script>

<template>
    <Head title="Calendar" />

    <AuthenticatedLayout>
        <div
            class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6"
        >
            <!-- Heading -->
            <div
                class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <p
                        class="text-sm font-medium text-indigo-600"
                    >
                        Tutor workspace
                    </p>

                    <h1
                        class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 md:text-3xl"
                    >
                        Calendar
                    </h1>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Plan lessons and keep track of your
                        teaching schedule.
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700"
                    @click="openLessonModal()"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-4 w-4"
                    >
                        <path
                            stroke-linecap="round"
                            d="M12 5v14M5 12h14"
                        />
                    </svg>

                    Add lesson
                </button>
            </div>

            <!-- Success -->
            <div
                v-if="successMessage"
                class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
            >
                {{ successMessage }}
            </div>

            <!-- Stats -->
            <div
                class="mb-6 grid gap-4 sm:grid-cols-3"
            >
                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <p
                        class="text-sm font-medium text-slate-500"
                    >
                        Today
                    </p>

                    <p
                        class="mt-2 text-2xl font-semibold text-slate-900"
                    >
                        {{ todayCount }}
                    </p>

                    <p
                        class="mt-1 text-xs text-slate-400"
                    >
                        Lessons today
                    </p>
                </div>

                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <p
                        class="text-sm font-medium text-slate-500"
                    >
                        This week
                    </p>

                    <p
                        class="mt-2 text-2xl font-semibold text-slate-900"
                    >
                        {{ weekCount }}
                    </p>

                    <p
                        class="mt-1 text-xs text-slate-400"
                    >
                        Lessons this week
                    </p>
                </div>

                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <p
                        class="text-sm font-medium text-slate-500"
                    >
                        Completed
                    </p>

                    <p
                        class="mt-2 text-2xl font-semibold text-slate-900"
                    >
                        {{ completedCount }}
                    </p>

                    <p
                        class="mt-1 text-xs text-slate-400"
                    >
                        Lessons this month
                    </p>
                </div>
            </div>

            <!-- Calendar -->
            <section
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2
                            class="font-medium text-slate-900"
                        >
                            Teaching schedule
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Click or drag over a time slot
                            to schedule a lesson.
                        </p>
                    </div>

                    <div
                        class="flex flex-wrap items-center gap-4 text-xs text-slate-500"
                    >
                        <div
                            class="flex items-center gap-2"
                        >
                            <span
                                class="h-2.5 w-2.5 rounded-full bg-violet-600"
                            ></span>

                            Scheduled
                        </div>

                        <div
                            class="flex items-center gap-2"
                        >
                            <span
                                class="h-2.5 w-2.5 rounded-full bg-emerald-500"
                            ></span>

                            Completed
                        </div>

                        <div
                            class="flex items-center gap-2"
                        >
                            <span
                                class="h-2.5 w-2.5 rounded-full bg-slate-400"
                            ></span>

                            Cancelled
                        </div>
                    </div>
                </div>

                <div
                    class="calendar-wrapper overflow-x-auto p-4 sm:p-5"
                >
                    <div class="min-w-[760px]">
                        <FullCalendar
                            :options="calendarOptions"
                        />
                    </div>
                </div>
            </section>
        </div>

        <!-- Add lesson modal -->
        <Modal
            :show="showLessonModal"
            max-width="lg"
            @close="closeLessonModal"
        >
            <div class="p-6">
                <div
                    class="flex items-start justify-between gap-4"
                >
                    <div>
                        <h2
                            class="text-lg font-semibold text-slate-900"
                        >
                            Add lesson
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Schedule a lesson with one of
                            your students.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        @click="closeLessonModal"
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
                                d="M6 6l12 12M18 6 6 18"
                            />
                        </svg>
                    </button>
                </div>

                <form
                    class="mt-6 space-y-5"
                    @submit.prevent="submitLesson"
                >
                    <!-- Student -->
                    <div>
                        <label
                            for="lesson-student"
                            class="mb-1.5 block text-sm font-medium text-slate-700"
                        >
                            Student
                        </label>

                        <select
                            id="lesson-student"
                            v-model="lessonForm.student_id"
                            required
                            class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                        >
                            <option
                                value=""
                                disabled
                            >
                                Select student
                            </option>

                            <option
                                v-for="student in students"
                                :key="student.id"
                                :value="student.id"
                            >
                                {{ student.name }}
                            </option>
                        </select>

                        <p
                            v-if="lessonForm.errors.student_id"
                            class="mt-1.5 text-xs text-red-600"
                        >
                            {{ lessonForm.errors.student_id }}
                        </p>
                    </div>

                    <!-- Subject -->
                    <div>
                        <label
                            for="lesson-subject"
                            class="mb-1.5 block text-sm font-medium text-slate-700"
                        >
                            Subject
                        </label>

                        <select
                            id="lesson-subject"
                            v-model="lessonForm.subject"
                            required
                            :disabled="!lessonForm.student_id"
                            class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2.5 text-sm disabled:cursor-not-allowed disabled:opacity-60 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                        >
                            <option
                                value=""
                                disabled
                            >
                                {{
                                    lessonForm.student_id
                                        ? 'Select subject'
                                        : 'Select student first'
                                }}
                            </option>

                            <option
                                v-for="subject in availableSubjects"
                                :key="subject"
                                :value="subject"
                            >
                                {{ subject }}
                            </option>
                        </select>

                        <p
                            v-if="lessonForm.errors.subject"
                            class="mt-1.5 text-xs text-red-600"
                        >
                            {{ lessonForm.errors.subject }}
                        </p>
                    </div>

                    <!-- Date -->
                    <div>
                        <label
                            for="lesson-date"
                            class="mb-1.5 block text-sm font-medium text-slate-700"
                        >
                            Date
                        </label>

                        <input
                            id="lesson-date"
                            v-model="lessonForm.date"
                            type="date"
                            required
                            class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                        />

                        <p
                            v-if="lessonForm.errors.date"
                            class="mt-1.5 text-xs text-red-600"
                        >
                            {{ lessonForm.errors.date }}
                        </p>
                    </div>

                    <!-- Time -->
                    <div
                        class="grid gap-4 sm:grid-cols-2"
                    >
                        <div>
                            <label
                                for="lesson-start"
                                class="mb-1.5 block text-sm font-medium text-slate-700"
                            >
                                Start time
                            </label>

                            <input
                                id="lesson-start"
                                v-model="lessonForm.start_time"
                                type="time"
                                required
                                step="900"
                                class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                            />

                            <p
                                v-if="lessonForm.errors.start_time"
                                class="mt-1.5 text-xs text-red-600"
                            >
                                {{ lessonForm.errors.start_time }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="lesson-end"
                                class="mb-1.5 block text-sm font-medium text-slate-700"
                            >
                                End time
                            </label>

                            <input
                                id="lesson-end"
                                v-model="lessonForm.end_time"
                                type="time"
                                required
                                step="900"
                                class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500"
                            />

                            <p
                                v-if="lessonForm.errors.end_time"
                                class="mt-1.5 text-xs text-red-600"
                            >
                                {{ lessonForm.errors.end_time }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-4 py-3"
                    >
                        <div>
                            <p
                                class="text-sm font-medium text-slate-700"
                            >
                                Status
                            </p>

                            <p
                                class="mt-0.5 text-xs text-slate-400"
                            >
                                New lessons are scheduled
                                automatically.
                            </p>
                        </div>

                        <span
                            class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-medium text-violet-700"
                        >
                            Scheduled
                        </span>
                    </div>

                    <div
                        class="flex justify-end gap-3 border-t border-slate-100 pt-5"
                    >
                        <button
                            type="button"
                            :disabled="lessonForm.processing"
                            class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50"
                            @click="closeLessonModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            :disabled="lessonForm.processing"
                            class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{
                                lessonForm.processing
                                    ? 'Scheduling...'
                                    : 'Add lesson'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Lesson details modal -->
        <Modal
            :show="showLessonDetailsModal"
            max-width="lg"
            @close="closeLessonDetails"
        >
            <div
                v-if="selectedLesson"
                class="p-6"
            >
                <!-- Header -->
                <div
                    class="flex items-start justify-between gap-4"
                >
                    <div>
                        <h2
                            class="text-lg font-semibold text-slate-900"
                        >
                            {{ selectedLesson.student_name }}
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Lesson details
                        </p>
                    </div>

                    <button
                        type="button"
                        :disabled="lessonActionProcessing"
                        class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:opacity-50"
                        @click="closeLessonDetails"
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
                                d="M6 6l12 12M18 6 6 18"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Details -->
                <div
                    class="mt-6 divide-y divide-slate-100 rounded-xl border border-slate-200"
                >
                    <div
                        class="flex items-center justify-between gap-4 px-4 py-3"
                    >
                        <span class="text-sm text-slate-500">
                            Student
                        </span>

                        <span
                            class="text-sm font-medium text-slate-900"
                        >
                            {{ selectedLesson.student_name }}
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between gap-4 px-4 py-3"
                    >
                        <span class="text-sm text-slate-500">
                            Subject
                        </span>

                        <span
                            class="text-sm font-medium text-slate-900"
                        >
                            {{ selectedLesson.subject || '—' }}
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between gap-4 px-4 py-3"
                    >
                        <span class="text-sm text-slate-500">
                            Date
                        </span>

                        <span
                            class="text-right text-sm font-medium text-slate-900"
                        >
                            {{
                                formatLessonDate(
                                    selectedLesson.start_time
                                )
                            }}
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between gap-4 px-4 py-3"
                    >
                        <span class="text-sm text-slate-500">
                            Time
                        </span>

                        <span
                            class="text-sm font-medium text-slate-900"
                        >
                            {{
                                formatLessonTime(
                                    selectedLesson.start_time
                                )
                            }}
                            –
                            {{
                                formatLessonTime(
                                    selectedLesson.end_time
                                )
                            }}
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between gap-4 px-4 py-3"
                    >
                        <span class="text-sm text-slate-500">
                            Status
                        </span>

                        <span
                            v-if="
                                selectedLesson.status ===
                                'scheduled'
                            "
                            class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-medium text-violet-700"
                        >
                            Scheduled
                        </span>

                        <span
                            v-else-if="
                                selectedLesson.status ===
                                'completed'
                            "
                            class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700"
                        >
                            Completed
                        </span>

                        <span
                            v-else
                            class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600"
                        >
                            Cancelled
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div
                    class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <button
                        type="button"
                        :disabled="lessonActionProcessing"
                        class="rounded-lg border border-red-200 bg-white px-4 py-2.5 text-sm font-medium text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="deleteLesson"
                    >
                        {{
                            lessonActionProcessing
                                ? 'Working...'
                                : 'Delete'
                        }}
                    </button>

                    <div
                        v-if="
                            selectedLesson.status ===
                            'scheduled'
                        "
                        class="flex flex-col gap-3 sm:flex-row"
                    >
                        <button
                            type="button"
                            :disabled="lessonActionProcessing"
                            class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                            @click="cancelLesson"
                        >
                            {{
                                lessonActionProcessing
                                    ? 'Updating...'
                                    : 'Cancel lesson'
                            }}
                        </button>

                        <button
                            type="button"
                            :disabled="lessonActionProcessing"
                            class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                            @click="markLessonCompleted"
                        >
                            {{
                                lessonActionProcessing
                                    ? 'Updating...'
                                    : 'Mark completed'
                            }}
                        </button>
                    </div>

                    <div
                        v-else
                        class="text-sm text-slate-400"
                    >
                        Lesson is
                        {{ selectedLesson.status }}.
                    </div>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style>
.tutorly-calendar-toolbar {
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.tutorly-calendar-title {
    color: #0f172a;
    font-size: 1rem;
    font-weight: 600;
}

.tutorly-calendar-section {
    gap: 0.375rem;
}

.tutorly-calendar-button {
    font-family: inherit;
}

.calendar-wrapper a {
    color: inherit;
    text-decoration: none;
}

.calendar-wrapper table {
    font-family: inherit;
    font-size: 0.875rem;
}

.calendar-wrapper [aria-current='date'] {
    font-weight: 600;
}

/*
|--------------------------------------------------------------------------
| Lesson colours
|--------------------------------------------------------------------------
*/

/* Scheduled = violet */
.calendar-wrapper .fc-event.lesson-scheduled {
    background: #7c3aed !important;
    border-color: #7c3aed !important;
    color: #ffffff !important;
}

/* Completed = green */
.calendar-wrapper .fc-event.lesson-completed {
    background: #10b981 !important;
    border-color: #10b981 !important;
    color: #ffffff !important;
}

/* Cancelled = grey */
.calendar-wrapper .fc-event.lesson-cancelled {
    background: #94a3b8 !important;
    border-color: #94a3b8 !important;
    color: #ffffff !important;
    opacity: 0.8;
}

.calendar-wrapper .fc-event.lesson-scheduled .fc-event-main,
.calendar-wrapper .fc-event.lesson-completed .fc-event-main,
.calendar-wrapper .fc-event.lesson-cancelled .fc-event-main {
    color: #ffffff !important;
}

.calendar-wrapper .fc-event {
    cursor: pointer;
    border-radius: 6px;
}
</style>