<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { show as showProjectRoute } from '@/routes/projects';
import type { LaravelPagination } from '@/types';
import type { Project } from '@/types/project';


defineProps<{
	projects: LaravelPagination<Project>;
}>();


const showProject = (projectId: number) => {
	return showProjectRoute(projectId).url;
};

</script>

<template>
	<table class="w-full text-left text-sm">
		<thead class="border-b text-xs uppercase text-muted-foreground">
			<tr>
				<th class="py-3 pr-4">Created</th>
				<th class="py-3 pr-4">Title</th>
				<th class="py-3 pr-4">Tickets</th>
			</tr>
		</thead>
		<tbody>
			<tr v-if="projects.data.length === 0">
				<td class="py-4 text-muted-foreground" colspan="4">
					No projects found.
				</td>
			</tr>
			<tr v-else v-for="project in projects.data" :key="project.id"
				class="relative border-b transition hover:bg-muted/50 last:border-b-0">
				<td class="py-3 pr-4 text-nowrap">{{ project.created_at }}</td>
				<td class="py-3 pr-4 max-w-[140px]">
					<span class="block truncate">
						<Link :href="showProject(project.id)" class="absolute inset-0" aria-label="Open project" />
						{{ project.title }}
					</span>
				</td>
				<td class="py-3 pr-4 text-nowrap">
					<span class="rounded p-1 font-bold">{{ project.tickets_total }}</span>
				</td>
			</tr>
		</tbody>
	</table>
</template>
