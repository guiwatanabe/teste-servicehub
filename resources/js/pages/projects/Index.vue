<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ProjectsTable from '@/components/ProjectsTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as projectsIndex } from '@/routes/projects';
import type { LaravelPagination } from '@/types';
import { type BreadcrumbItem } from '@/types';
import type { Project } from '@/types/project';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: projectsIndex().url,
    },
];

defineProps<{ projects: LaravelPagination<Project> }>();
</script>

<template>

    <Head title="Projects" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <h1 class="text-3xl font-bold">Projects</h1>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <ProjectsTable :projects="projects" />
                    </div>

                    <div class="mt-4 flex items-center justify-between text-sm">
                        <p class="text-muted-foreground">
                            Page {{ projects.meta.current_page }} of {{ projects.meta.last_page }}
                        </p>
                        <div class="flex gap-2">
                            <Link v-if="projects.links.prev" :href="projects.links.prev"
                                class="rounded-md border px-3 py-1 hover:bg-muted">
                                Previous
                            </Link>
                            <Link v-if="projects.links.next" :href="projects.links.next"
                                class="rounded-md border px-3 py-1 hover:bg-muted">
                                Next
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
