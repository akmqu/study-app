<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';

import {
    Head,
    useForm,
} from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(
        route(
            'password.confirm'
        ),
        {
            onFinish: () =>
                form.reset(),
        }
    );
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password" />

        <div>
            <h1
                class="text-2xl font-semibold tracking-tight text-slate-950"
            >
                Confirm your password
            </h1>

            <p
                class="mt-2 text-sm leading-6 text-slate-500"
            >
                Enter your password before continuing to this secure area.
            </p>
        </div>

        <form
            class="mt-6 space-y-5"
            @submit.prevent="submit"
        >
            <div>
                <label
                    for="password"
                    class="block text-sm font-medium text-slate-700"
                >
                    Password
                </label>

                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autofocus
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    class="mt-2 block w-full rounded-md border-slate-300 bg-white px-3 py-2.5 text-sm placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500"
                />

                <InputError
                    class="mt-1.5"
                    :message="form.errors.password"
                />
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex w-full items-center justify-center rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800 disabled:opacity-40"
            >
                {{
                    form.processing
                        ? 'Confirming...'
                        : 'Confirm password'
                }}
            </button>
        </form>
    </GuestLayout>
</template>