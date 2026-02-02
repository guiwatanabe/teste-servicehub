<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ status: Number });

const title = computed(
    () =>
        ({
            503: 'Service Unavailable',
            500: 'Server Error',
            404: 'Page Not Found',
            403: 'Forbidden',
        })[props.status] ?? 'Something went wrong',
);

const description = computed(
    () =>
        ({
            503: 'Sorry, we are doing some maintenance. Please check back soon.',
            500: 'Whoops, something went wrong on our servers.',
            404: 'Sorry, the page you are looking for could not be found.',
            403: 'Sorry, you are forbidden from accessing this page.',
        })[props.status] ?? 'Please try again later.',
);
</script>

<template>
    <Head :title="`${props.status ?? 500} • ${title}`" />

    <div
        class="flex min-h-screen items-center justify-center bg-background p-6"
    >
        <div
            class="w-full max-w-md rounded-xl border border-border/70 bg-neutral-900 p-6 text-center shadow-sm"
        >
            <p class="text-sm font-medium text-muted-foreground">
                Error {{ props.status ?? 500 }}
            </p>
            <h1 class="mt-2 text-2xl font-semibold">{{ title }}</h1>
            <p class="mt-3 text-sm text-muted-foreground">
                {{ description }}
            </p>

            <Link
                href="/dashboard"
                class="mt-6 inline-flex items-center justify-center rounded-md border bg-neutral-500 px-4 py-2 text-sm font-medium transition hover:bg-neutral-700"
            >
                Back to Dashboard
            </Link>
        </div>
    </div>
</template>
