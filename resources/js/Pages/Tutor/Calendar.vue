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
    onMounted,
    onUnmounted,
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

const successMessage = computed(
    () => page.props.flash?.success ?? null
);

/*
|--------------------------------------------------------------------------
| Timezone
|--------------------------------------------------------------------------
*/

const browserTimeZone =
    Intl.DateTimeFormat()
        .resolvedOptions()
        .timeZone || 'UTC';

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
    timezone: browserTimeZone,
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

    lessonForm.timezone =
        Intl.DateTimeFormat()
            .resolvedOptions()
            .timeZone || 'UTC';

    showLessonModal.value = true;
};

const closeLessonModal = () => {
    if (lessonForm.processing) {
        return;
    }

    showLessonModal.value = false;

    lessonForm.reset();

    lessonForm.timezone =
        Intl.DateTimeFormat()
            .resolvedOptions()
            .timeZone || 'UTC';

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

                lessonForm.timezone =
                    Intl.DateTimeFormat()
                        .resolvedOptions()
                        .timeZone || 'UTC';

                lessonForm.clearErrors();
            },
        }
    );
};

/*
|--------------------------------------------------------------------------
| Calendar interaction
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

    openLessonModal(
        start,
        end
    );
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
| Lesson actions
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

const getLessonColor = (status) => {
    if (status === 'completed') {
        return '#64748b';
    }

    if (status === 'cancelled') {
        return '#cbd5e1';
    }

    return '#0f172a';
};

const calendarEvents = computed(() => {
    return props.lessons.map((lesson) => {
        return {
            id: String(lesson.id),

            title: lesson.subject
                ? `${lesson.student_name} · ${lesson.subject}`
                : lesson.student_name,

            start: lesson.start_time,

            end: lesson.end_time,

            color:
                getLessonColor(
                    lesson.status
                ),

            contrastColor: '#ffffff',

            display: 'block',

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
| Automatic refresh
|--------------------------------------------------------------------------
*/

let lessonRefreshTimer = null;

onMounted(() => {
    lessonRefreshTimer = window.setInterval(
        () => {
            router.reload({
                only: ['lessons'],
                preserveScroll: true,
                preserveState: true,
            });
        },
        60 * 1000
    );
});

onUnmounted(() => {
    if (lessonRefreshTimer) {
        window.clearInterval(
            lessonRefreshTimer
        );
    }
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

    timeZone: 'local',

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

    eventDisplay: 'block',

    dateClick:
        handleDateClick,

    select:
        handleSelect,

    eventClick:
        openLessonDetails,

    events:
        calendarEvents.value,

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
        <div class="mx-auto max-w-6xl">
            <!-- Heading -->
            <header
                class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-950"
                    >
                        Calendar
                    </h1>

                    <p
                        class="mt-2 text-sm text-slate-500"
                    >
                        Schedule and manage your lessons.
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800"
                    @click="openLessonModal()"
                >
                    Add lesson
                </button>
            </header>

            <!-- Success -->
            <div
                v-if="successMessage"
                class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ successMessage }}
            </div>

            <!-- Calendar -->
            <section class="mt-8">
                <div
                    class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p
                        class="text-sm text-slate-500"
                    >
                        Click or drag over a time slot to
                        schedule a lesson.
                    </p>

                    <div
                        class="flex items-center gap-4 text-xs text-slate-500"
                    >
                        <span
                            class="flex items-center gap-2"
                        >
                            <span
                                class="h-2 w-2 rounded-full bg-slate-900"
                            ></span>

                            Scheduled
                        </span>

                        <span
                            class="flex items-center gap-2"
                        >
                            <span
                                class="h-2 w-2 rounded-full bg-slate-500"
                            ></span>

                            Completed
                        </span>

                        <span
                            class="flex items-center gap-2"
                        >
                            <span
                                class="h-2 w-2 rounded-full bg-slate-300"
                            ></span>

                            Cancelled
                        </span>
                    </div>
                </div>

                <div
                    class="calendar-wrapper overflow-x-auto border-y border-slate-200 py-5"
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
                            class="text-lg font-semibold text-slate-950"
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
                        class="text-sm text-slate-500 transition hover:text-slate-950"
                        @click="closeLessonModal"
                    >
                        Close
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
                            class="block w-full rounded-md border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-500 focus:ring-slate-500"
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
                            class="block w-full rounded-md border-slate-300 bg-white px-3 py-2 text-sm disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400 focus:border-slate-500 focus:ring-slate-500"
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
                            class="block w-full rounded-md border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-500 focus:ring-slate-500"
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
                                class="block w-full rounded-md border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-500 focus:ring-slate-500"
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
                                class="block w-full rounded-md border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-500 focus:ring-slate-500"
                            />

                            <p
                                v-if="lessonForm.errors.end_time"
                                class="mt-1.5 text-xs text-red-600"
                            >
                                {{ lessonForm.errors.end_time }}
                            </p>
                        </div>
                    </div>

                    <p
                        class="text-xs text-slate-400"
                    >
                        Timezone:
                        {{ browserTimeZone }}
                    </p>

                    <!-- Actions -->
                    <div
                        class="flex justify-end gap-3 border-t border-slate-200 pt-5"
                    >
                        <button
                            type="button"
                            :disabled="lessonForm.processing"
                            class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-40"
                            @click="closeLessonModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            :disabled="lessonForm.processing"
                            class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-40"
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
                            class="text-lg font-semibold text-slate-950"
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
                        class="text-sm text-slate-500 transition hover:text-slate-950 disabled:opacity-40"
                        @click="closeLessonDetails"
                    >
                        Close
                    </button>
                </div>

                <!-- Details -->
                <dl
                    class="mt-6 divide-y divide-slate-200"
                >
                    <div
                        class="flex items-center justify-between gap-4 py-4"
                    >
                        <dt
                            class="text-sm text-slate-500"
                        >
                            Student
                        </dt>

                        <dd
                            class="text-sm font-medium text-slate-900"
                        >
                            {{ selectedLesson.student_name }}
                        </dd>
                    </div>

                    <div
                        class="flex items-center justify-between gap-4 py-4"
                    >
                        <dt
                            class="text-sm text-slate-500"
                        >
                            Subject
                        </dt>

                        <dd
                            class="text-sm font-medium text-slate-900"
                        >
                            {{
                                selectedLesson.subject ||
                                '—'
                            }}
                        </dd>
                    </div>

                    <div
                        class="flex items-center justify-between gap-4 py-4"
                    >
                        <dt
                            class="text-sm text-slate-500"
                        >
                            Date
                        </dt>

                        <dd
                            class="text-right text-sm font-medium text-slate-900"
                        >
                            {{
                                formatLessonDate(
                                    selectedLesson.start_time
                                )
                            }}
                        </dd>
                    </div>

                    <div
                        class="flex items-center justify-between gap-4 py-4"
                    >
                        <dt
                            class="text-sm text-slate-500"
                        >
                            Time
                        </dt>

                        <dd
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
                        </dd>
                    </div>

                    <div
                        class="flex items-center justify-between gap-4 py-4"
                    >
                        <dt
                            class="text-sm text-slate-500"
                        >
                            Status
                        </dt>

                        <dd>
                            <span
                                v-if="
                                    selectedLesson.status ===
                                    'scheduled'
                                "
                                class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700"
                            >
                                Scheduled
                            </span>

                            <span
                                v-else-if="
                                    selectedLesson.status ===
                                    'completed'
                                "
                                class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700"
                            >
                                Completed
                            </span>

                            <span
                                v-else
                                class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500"
                            >
                                Cancelled
                            </span>
                        </dd>
                    </div>
                </dl>

                <!-- Actions -->
                <div
                    class="mt-7 flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <button
                        type="button"
                        :disabled="lessonActionProcessing"
                        class="text-left text-sm font-medium text-red-600 transition hover:text-red-700 disabled:opacity-40"
                        @click="deleteLesson"
                    >
                        {{
                            lessonActionProcessing
                                ? 'Working...'
                                : 'Delete lesson'
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
                            class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-40"
                            @click="cancelLesson"
                        >
                            Cancel lesson
                        </button>

                        <button
                            type="button"
                            :disabled="lessonActionProcessing"
                            class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800 disabled:opacity-40"
                            @click="markLessonCompleted"
                        >
                            Mark completed
                        </button>
                    </div>

                    <p
                        v-else
                        class="text-sm text-slate-400"
                    >
                        Lesson is
                        {{ selectedLesson.status }}.
                    </p>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style>
.tutorly-calendar-toolbar {
    gap: 0.75rem;
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

.calendar-wrapper .fc-event {
    cursor: pointer;
    border-radius: 4px;
}

.calendar-wrapper button {
    border-radius: 6px;
}
</style>