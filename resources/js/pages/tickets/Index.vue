<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import TicketsTable from '@/components/TicketsTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as ticketsIndex } from '@/routes/tickets';
import { getTickets } from '@/services';
import type { LaravelPagination } from '@/types';
import { type BreadcrumbItem } from '@/types';
import type { Ticket } from '@/types/ticket';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tickets',
        href: ticketsIndex().url,
    },
];

defineProps<{ canCreateTickets: boolean }>();

const tickets = ref<LaravelPagination<Ticket> | null>(null);
const isLoadingTickets = ref(false);
const perPage = 10;
const currentPage = ref(1);
const statusFilter = ref('');
const priorityFilter = ref('');
const totalPages = computed(() => tickets.value?.meta?.last_page ?? 1);

const fetchTickets = async (page = 1) => {
    isLoadingTickets.value = true;
    try {
        tickets.value = await getTickets(perPage, page, {
            status: statusFilter.value || undefined,
            priority: priorityFilter.value || undefined
        });
        currentPage.value = page;
    } finally {
        isLoadingTickets.value = false;
    }
};

onMounted(async () => {
    await fetchTickets(1);
});

</script>

<template>

    <Head title="Tickets" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold">Tickets</h1>
                <div v-if="canCreateTickets">
                    <Link :href="`/tickets/create`"
                        class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:opacity-50">
                        Create Ticket
                    </Link>
                </div>
            </div>

            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <div class="p-4">
                    <div class="overflow-x-auto">

                        <div class="mb-4 flex flex-wrap items-center gap-4">
                            <div class="flex items-center gap-2">
                                <label for="status-filter" class="text-sm text-muted-foreground">Status</label>
                                <select id="status-filter" v-model="statusFilter"
                                    class="rounded-md border border-input bg-background px-2 py-1 text-sm text-foreground shadow-sm">
                                    <option value="">All</option>
                                    <option value="open">Open</option>
                                    <option value="in_progress">In progress</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-2">
                                <label for="priority-filter" class="text-sm text-muted-foreground">Priority</label>
                                <select id="priority-filter" v-model="priorityFilter"
                                    class="rounded-md border border-input bg-background px-2 py-1 text-sm text-foreground shadow-sm">
                                    <option value="">All</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>

                            <button type="button"
                                class="rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm hover:bg-muted"
                                :disabled="isLoadingTickets" @click="fetchTickets(1)">
                                Apply filters
                            </button>
                        </div>

                        <div v-if="isLoadingTickets" class="text-sm text-muted-foreground">
                            Loading tickets...
                        </div>
                        <TicketsTable v-else :tickets="tickets?.data ?? []" :showProject="true" />

                        <div class="mt-4 flex items-center justify-between text-sm">
                            <p class="text-muted-foreground">
                                Page {{ currentPage }} of {{ totalPages }}
                            </p>
                            <p>
                                Showing {{ tickets?.meta?.from ?? 0 }} to {{ tickets?.meta?.to ?? 0 }} of
                                {{ tickets?.meta?.total ?? 0 }} results
                            </p>
                            <div class="flex gap-2">
                                <button type="button"
                                    class="rounded-md border px-3 py-1 hover:bg-muted disabled:opacity-50"
                                    :disabled="currentPage <= 1 || isLoadingTickets"
                                    @click="fetchTickets(currentPage - 1)">
                                    Previous
                                </button>
                                <button type="button"
                                    class="rounded-md border px-3 py-1 hover:bg-muted disabled:opacity-50"
                                    :disabled="currentPage >= totalPages || isLoadingTickets"
                                    @click="fetchTickets(currentPage + 1)">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
