<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useTicketStyles } from '@/composables/useTicketStyles';
import { show as showTicketRoute } from '@/routes/tickets';
import type { Ticket } from '@/types/ticket';

type TicketsProp = Ticket[] | { data: Ticket[] };

const props = withDefaults(
	defineProps<{
		tickets: TicketsProp;
		showProject?: boolean;
	}>(),
	{
		showProject: true,
	},
);

const ticketsList = computed(() =>
	Array.isArray(props.tickets) ? props.tickets : props.tickets.data ?? [],
)

const showTicket = (ticketId: number) => {
	return showTicketRoute(ticketId).url;
};

const { getPriorityColor, getStatusColor, getDateColor } = useTicketStyles();

</script>

<template>
	<table class="w-full text-left text-sm">
		<thead class="border-b text-xs uppercase text-muted-foreground">
			<tr>
				<th class="py-3 pr-4">Created</th>
				<th class="py-3 pr-4">Title</th>
				<th v-if="showProject" class="py-3 pr-4">Project</th>
				<th class="py-3 pr-4">Priority</th>
				<th class="py-3 pr-4">Status</th>
				<th class="py-3">Due Date</th>
			</tr>
		</thead>
		<tbody>
			<tr v-if="ticketsList.length === 0">
				<td class="py-4 text-muted-foreground" :colspan="showProject ? 7 : 6">
					No tickets found.
				</td>
			</tr>
			<tr v-else v-for="ticket in ticketsList" :key="ticket.id"
				class="relative border-b transition hover:bg-muted/50 last:border-b-0">
				<td class="py-3 pr-4 text-nowrap">{{ ticket.created_at }}</td>
				<td class="py-3 pr-4 max-w-[140px]">
					<span class="block truncate">
						<Link :href="showTicket(ticket.id)" class="absolute inset-0" aria-label="Open ticket" />
						{{ ticket.title }}
					</span>
				</td>
				<td v-if="showProject" class="py-3 pr-4 max-w-[140px]">
					<span class="block truncate">
						{{ ticket.project?.title ?? '-' }}
					</span>
				</td>
				<td class="py-3 pr-4 text-nowrap">
					<span :class="`rounded p-1 font-bold ${getPriorityColor(ticket.priority)}`">{{ ticket.priority
					}}</span>
				</td>
				<td class="py-3 pr-4 text-nowrap">
					<span :class="`rounded p-1 font-bold ${getStatusColor(ticket.status)}`">{{ ticket.status }}</span>
				</td>
				<td class="py-3 text-nowrap">
					<span :class="getDateColor(ticket.status, ticket.due_date ?? null)">{{ ticket.due_date ?? '-'
					}}</span>
				</td>
			</tr>
		</tbody>
	</table>
</template>
