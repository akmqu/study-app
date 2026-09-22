<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
});

const page = usePage();

const successMessage = computed(
    () => page.props.flash?.success ?? null
);

const form = useForm({
    default_lesson_duration:
        props.settings.default_lesson_duration,

    lesson_reminders_enabled:
        props.settings.lesson_reminders_enabled,

    lesson_reminder_minutes:
        props.settings.lesson_reminder_minutes,

    default_billing_type:
        props.settings.default_billing_type,
});

const submit = () => {
    form.patch(
        route('tutor.settings.update'),
        {
            preserveScroll: true,
        }
    );
};
</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-3xl">
            <header>
                <h1
                    class="text-2xl font-semibold tracking-tight text-slate-950"
                >
                    Settings
                </h1>

                <p
                    class="mt-1.5 text-sm text-slate-500"
                >
                    Configure default lesson and billing behaviour.
                </p>
            </header>

            <div
                v-if="successMessage"
                class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ successMessage }}
            </div>

            <form
                class="mt-6 space-y-6"
                @submit.prevent="submit"
            >
                <!-- Lessons -->
                <section
                    class="rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="border-b border-slate-200 px-6 py-5"
                    >
                        <h2
                            class="text-base font-semibold text-slate-900"
                        >
                            Lessons
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Default values used when creating lessons.
                        </p>
                    </div>

                    <div class="px-6 py-5">
                        <label
                            for="lesson-duration"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Default lesson duration
                        </label>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Used as the default length of a new lesson.
                        </p>

                        <select
                            id="lesson-duration"
                            v-model.number="
                                form.default_lesson_duration
                            "
                            class="mt-3 block w-full rounded-md border-slate-300 sm:max-w-xs"
                        >
                            <option :value="30">
                                30 minutes
                            </option>

                            <option :value="45">
                                45 minutes
                            </option>

                            <option :value="60">
                                60 minutes
                            </option>

                            <option :value="90">
                                90 minutes
                            </option>

                            <option :value="120">
                                120 minutes
                            </option>
                        </select>

                        <p
                            v-if="
                                form.errors.default_lesson_duration
                            "
                            class="mt-2 text-sm text-red-600"
                        >
                            {{
                                form.errors.default_lesson_duration
                            }}
                        </p>
                    </div>
                </section>

                <!-- Reminders -->
                <section
                    class="rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="border-b border-slate-200 px-6 py-5"
                    >
                        <h2
                            class="text-base font-semibold text-slate-900"
                        >
                            Lesson reminders
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Control automatic reminder emails before lessons.
                        </p>
                    </div>

                    <div class="divide-y divide-slate-200">
                        <div
                            class="flex items-start justify-between gap-6 px-6 py-5"
                        >
                            <div>
                                <label
                                    for="lesson-reminders"
                                    class="text-sm font-medium text-slate-900"
                                >
                                    Send lesson reminders
                                </label>

                                <p
                                    class="mt-1 text-sm text-slate-500"
                                >
                                    Automatically send students an email before a lesson.
                                </p>
                            </div>

                            <input
                                id="lesson-reminders"
                                v-model="
                                    form.lesson_reminders_enabled
                                "
                                type="checkbox"
                                class="mt-1 h-5 w-5 rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                            />
                        </div>

                        <div class="px-6 py-5">
                            <label
                                for="reminder-time"
                                class="block text-sm font-medium text-slate-700"
                            >
                                Reminder time
                            </label>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                How long before a lesson the reminder should be sent.
                            </p>

                            <select
                                id="reminder-time"
                                v-model.number="
                                    form.lesson_reminder_minutes
                                "
                                :disabled="
                                    !form.lesson_reminders_enabled
                                "
                                class="mt-3 block w-full rounded-md border-slate-300 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400 sm:max-w-xs"
                            >
                                <option :value="15">
                                    15 minutes before
                                </option>

                                <option :value="30">
                                    30 minutes before
                                </option>

                                <option :value="60">
                                    60 minutes before
                                </option>

                                <option :value="120">
                                    2 hours before
                                </option>
                            </select>

                            <p
                                v-if="
                                    form.errors.lesson_reminder_minutes
                                "
                                class="mt-2 text-sm text-red-600"
                            >
                                {{
                                    form.errors.lesson_reminder_minutes
                                }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Billing -->
                <section
                    class="rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="border-b border-slate-200 px-6 py-5"
                    >
                        <h2
                            class="text-base font-semibold text-slate-900"
                        >
                            Billing
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Choose the default billing model for students.
                        </p>
                    </div>

                    <div class="px-6 py-5">
                        <label
                            for="billing-type"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Default billing type
                        </label>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            This can still be changed individually for a student.
                        </p>

                        <select
                            id="billing-type"
                            v-model="
                                form.default_billing_type
                            "
                            class="mt-3 block w-full rounded-md border-slate-300 sm:max-w-xs"
                        >
                            <option value="per_lesson">
                                Per lesson
                            </option>

                            <option value="monthly">
                                Monthly
                            </option>
                        </select>

                        <p
                            v-if="
                                form.errors.default_billing_type
                            "
                            class="mt-2 text-sm text-red-600"
                        >
                            {{
                                form.errors.default_billing_type
                            }}
                        </p>
                    </div>
                </section>

                <!-- Save -->
                <div
                    class="flex items-center justify-end gap-4"
                >
                    <span
                        v-if="form.processing"
                        class="text-sm text-slate-500"
                    >
                        Saving...
                    </span>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center justify-center rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Save settings
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>