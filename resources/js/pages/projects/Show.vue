<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import TicketsTable from '@/components/TicketsTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { getProjectTickets } from '@/services/projects.service';
import { type BreadcrumbItem } from '@/types';
import type { LaravelPagination } from '@/types';
import type { Project } from '@/types/project';
import type { Ticket } from '@/types/ticket';
import { index as projectsIndex } from '@/routes/projects';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: projectsIndex().url,
    },
];

const props = defineProps<{ project: Project | { data: Project } }>();

const project = computed(() =>
    'data' in props.project ? props.project.data : props.project,
);

const tickets = ref<LaravelPagination<Ticket> | null>(null);
const isLoadingTickets = ref(false);
const perPage = 10;
const currentPage = ref(1);

const totalPages = computed(() => tickets.value?.meta?.last_page ?? 1);
const totalTickets = computed(() => project.value?.tickets_total ?? 0);

const fetchTickets = async (page = 1) => {
    isLoadingTickets.value = true;
    try {
        tickets.value = await getProjectTickets(
            project.value.id,
            perPage,
            page,
        );
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
    <Head :title="`Project • ${project.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <h1 class="text-3xl font-bold">Project Details</h1>

            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <div class="p-4">
                    <div class="space-y-6">
                        <div class="grid auto-rows-min gap-4 md:grid-cols-2">
                            <div
                                class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                            >
                                <div class="p-4">
                                    <h2 class="text-2xl font-semibold">
                                        {{ project.title }}
                                    </h2>
                                    <p
                                        class="mt-2 text-sm text-muted-foreground"
                                    >
                                        {{
                                            project.details ??
                                            'No description provided.'
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                            >
                                <div class="p-4">
                                    <h2 class="text-2xl font-semibold">
                                        Tickets
                                    </h2>
                                    <p
                                        class="mt-2 text-sm text-muted-foreground"
                                    >
                                        Total: {{ totalTickets }}
                                    </p>
                                    <ul
                                        class="mt-2 list-inside list-disc text-sm"
                                    >
                                        <li>
                                            Open:
                                            {{ project.tickets_open ?? 0 }}
                                        </li>
                                        <li>
                                            In Progress:
                                            {{
                                                project.tickets_in_progress ?? 0
                                            }}
                                        </li>
                                        <li>
                                            Closed:
                                            {{ project.tickets_closed ?? 0 }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <div
                                v-if="isLoadingTickets"
                                class="text-sm text-muted-foreground"
                            >
                                Loading tickets...
                            </div>
                            <TicketsTable
                                v-else
                                :tickets="tickets?.data ?? []"
                                :showProject="false"
                            />

                            <div
                                class="mt-4 flex items-center justify-between text-sm"
                            >
                                <p class="text-muted-foreground">
                                    Page {{ currentPage }} of {{ totalPages }}
                                </p>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="rounded-md border px-3 py-1 hover:bg-muted disabled:opacity-50"
                                        :disabled="
                                            currentPage <= 1 || isLoadingTickets
                                        "
                                        @click="fetchTickets(currentPage - 1)"
                                    >
                                        Previous
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md border px-3 py-1 hover:bg-muted disabled:opacity-50"
                                        :disabled="
                                            currentPage >= totalPages ||
                                            isLoadingTickets
                                        "
                                        @click="fetchTickets(currentPage + 1)"
                                    >
                                        Next
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
