<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TicketsTable from '@/components/TicketsTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { LaravelPagination } from '@/types';
import { type BreadcrumbItem } from '@/types';
import type { Ticket } from '@/types/ticket';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const props = defineProps<{ tickets: LaravelPagination<Ticket>, statusCounts: Record<string, number> }>();

</script>

<template>

    <Head title="Employee Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <h1 class="text-3xl font-bold">Employee Dashboard</h1>

            <div class="grid auto-rows-min gap-4 md:grid-cols-2">
                <div class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="p-4">
                        <h3 class="font-semibold">Open / In Progress</h3>
                        <div class="mt-3 text-2xl font-semibold">
                            <span>{{ props.statusCounts['open'] + props.statusCounts['in_progress'] }}</span>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            tickets assigned to me
                        </p>
                    </div>
                </div>

                <div
                    class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="p-4">
                        <h3 class="font-semibold">Closed Tickets</h3>
                        <div class="mt-3 text-2xl font-semibold">
                            <span>{{ props.statusCounts['closed'] }}</span>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            tickets assigned to me
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <div class="p-4">
                    <h2 class="mb-4 text-2xl font-bold">Recent Tickets</h2>
                    <div class="overflow-x-auto">
                        <TicketsTable :tickets="props.tickets" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
