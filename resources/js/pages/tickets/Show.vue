<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import TicketDetailsInfo from '@/components/TicketDetailsInfo.vue';
import { useTicketStyles } from '@/composables/useTicketStyles';
import AppLayout from '@/layouts/AppLayout.vue';
import { closeTicket as closeTicketRequest } from '@/services/tickets.service';
import { type BreadcrumbItem } from '@/types';
import type { Ticket } from '@/types/ticket';
import { index as ticketsIndex } from '@/routes/tickets';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tickets',
        href: ticketsIndex().url,
    },
];

const props = defineProps<{ ticket: Ticket | { data: Ticket } }>();

const ticket = computed(() =>
    'data' in props.ticket ? props.ticket.data : props.ticket,
);

const { getStatusColor, getPriorityColor, getDateColor } = useTicketStyles();

const closeTicket = async () => {
    try {
        await closeTicketRequest(ticket.value.id).then(() => {
            window.location.reload();
        });
    } catch (error) {
        console.error('Failed to close the ticket:', error);
    }
};
</script>

<template>
    <Head :title="`Ticket • ${ticket.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">Ticket Details</h1>
                <div v-if="ticket.status !== 'closed'">
                    <button
                        class="rounded-md border border-red-400 px-3 py-1 hover:bg-muted dark:border-red-700"
                        @click="closeTicket"
                    >
                        Close Ticket
                    </button>
                </div>
            </div>

            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <div class="p-4">
                    <div class="space-y-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                :class="`rounded-full border px-2 py-0.5 text-xs font-medium ${getStatusColor(ticket.status)}`"
                            >
                                {{ ticket.status }}
                            </span>
                            <span
                                :class="`rounded-full border px-2 py-0.5 text-xs font-medium ${getPriorityColor(ticket.priority)}`"
                            >
                                {{ ticket.priority }}
                            </span>
                        </div>

                        <div>
                            <h2 class="text-2xl font-semibold">
                                {{ ticket.title }}
                            </h2>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <h3
                                    class="text-sm font-semibold text-muted-foreground"
                                >
                                    Project
                                </h3>
                                <p class="text-sm">
                                    {{ ticket.project?.title ?? '-' }}
                                </p>
                            </div>
                            <div>
                                <h3
                                    class="text-sm font-semibold text-muted-foreground"
                                >
                                    Assigned To
                                </h3>
                                <p class="text-sm">
                                    {{ ticket.recipient?.name ?? '-' }}
                                </p>
                            </div>
                            <div>
                                <h3
                                    class="text-sm font-semibold text-muted-foreground"
                                >
                                    Created
                                </h3>
                                <p class="text-sm">{{ ticket.created_at }}</p>
                            </div>
                            <div v-if="ticket.status == 'closed'">
                                <h3
                                    class="text-sm font-semibold text-muted-foreground"
                                >
                                    Closed At
                                </h3>
                                <p
                                    :class="
                                        getDateColor(
                                            ticket.status,
                                            ticket.due_date ?? null,
                                        )
                                    "
                                    class="text-sm"
                                >
                                    {{ ticket.closed_at }}
                                </p>
                            </div>
                            <div v-else>
                                <h3
                                    class="text-sm font-semibold text-muted-foreground"
                                >
                                    Due Date
                                </h3>
                                <p
                                    :class="
                                        getDateColor(
                                            ticket.status,
                                            ticket.due_date ?? null,
                                        )
                                    "
                                    class="text-sm"
                                >
                                    {{ ticket.due_date ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <h3
                                class="text-sm font-semibold text-muted-foreground"
                            >
                                Details
                            </h3>
                            <div>
                                <TicketDetailsInfo
                                    v-if="ticket.ticket_detail"
                                    :ticketDetail="ticket.ticket_detail"
                                />
                                <span v-else>No details available.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
