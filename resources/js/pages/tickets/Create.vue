<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as ticketsIndex, store as ticketsStore } from '@/routes/tickets';
import { type BreadcrumbItem } from '@/types';

const { props } = usePage();
const allProjects = props.projects as any[];
const allTeamMembers = props.teamMembers as any[];

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tickets',
        href: ticketsIndex().url,
    },
    {
        title: 'Create',
        href: '#',
    },
];

// Calculate default due date (today + 1 month)
const getDefaultDueDate = () => {
    const today = new Date();
    const nextMonth = new Date(today.setMonth(today.getMonth() + 1));
    return nextMonth.toISOString().split('T')[0];
};

const form = useForm<{
    title: string;
    project_id: string | number;
    assigned_to: string | number;
    priority: string;
    status: string;
    due_date: string;
    attachment: File | null;
}>({
    title: '',
    project_id: '',
    assigned_to: '',
    priority: 'medium',
    status: 'open',
    due_date: getDefaultDueDate(),
    attachment: null,
});

const submit = () => {
    form.post(ticketsStore().url, {
        onSuccess: () => {
            
        },
    });
};
</script>

<template>

    <Head title="Create Ticket" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold">Create Ticket</h1>
            </div>

            <div class="relative rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <div class="p-6">
                    <form @submit.prevent="submit" class="space-y-6 max-w-2xl">
                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium mb-2" for="title">Title *</label>
                            <input id="title" v-model="form.title" type="text"
                                class="rounded-md border border-input bg-background px-2 py-1 text-sm text-foreground shadow-sm"
                                placeholder="Ticket title" required />
                            <p v-if="form.errors.title" class="text-sm text-red-600 mt-1">
                                {{ form.errors.title }}
                            </p>
                        </div>

                        <!-- Project -->
                        <div>
                            <label class="block text-sm font-medium mb-2" for="project_id">Project *</label>
                            <select id="project_id" v-model="form.project_id"
                                class="rounded-md border border-input bg-background px-2 py-1 text-sm text-foreground shadow-sm"
                                required>
                                <option value="">Select a project</option>
                                <option v-for="project in allProjects" :key="project.id" :value="project.id">
                                    {{ project.title }}
                                </option>
                            </select>
                            <p v-if="form.errors.project_id" class="text-sm text-red-600 mt-1">
                                {{ form.errors.project_id }}
                            </p>
                        </div>

                        <!-- Priority -->
                        <div>
                            <label class="block text-sm font-medium mb-2" for="priority">Priority</label>
                            <select id="priority" v-model="form.priority"
                                class="rounded-md border border-input bg-background px-2 py-1 text-sm text-foreground shadow-sm">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                            <p v-if="form.errors.priority" class="text-sm text-red-600 mt-1">
                                {{ form.errors.priority }}
                            </p>
                        </div>

                        <!-- Due Date -->
                        <div>
                            <label class="block text-sm font-medium mb-2" for="due_date">Due Date</label>
                            <input id="due_date" v-model="form.due_date" type="date"
                                class="rounded-md border border-input bg-background px-2 py-1 text-sm text-foreground shadow-sm" />
                            <p v-if="form.errors.due_date" class="text-sm text-red-600 mt-1">
                                {{ form.errors.due_date }}
                            </p>
                        </div>

                        <!-- Assigned To -->
                        <div>
                            <label class="block text-sm font-medium mb-2" for="assigned_to">Assign To</label>
                            <select id="assigned_to" v-model="form.assigned_to"
                                class="rounded-md border border-input bg-background px-2 py-1 text-sm text-foreground shadow-sm">
                                <option value="">Select a team member</option>
                                <option v-for="member in allTeamMembers" :key="member.id" :value="member.id">
                                    {{ member.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.assigned_to" class="text-sm text-red-600 mt-1">
                                {{ form.errors.assigned_to }}
                            </p>
                        </div>

                        <!-- Attachment -->
                        <div>
                            <label class="block text-sm font-medium mb-2" for="attachment">Attachment</label>
                            <input
                                id="attachment"
                                type="file"
                                accept=".json,.txt"
                                @input="form.attachment = ($event.target as HTMLInputElement).files?.[0] || null"
                                class="block w-full text-sm text-foreground file:mr-4 file:rounded-md file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-blue-700 dark:file:bg-blue-500 dark:hover:file:bg-blue-600"
                            />
                            <p class="text-xs text-muted-foreground mt-1">
                                Only JSON or TXT files, max 4MB
                            </p>
                            <p v-if="form.errors.attachment" class="text-sm text-red-600 mt-1">
                                {{ form.errors.attachment }}
                            </p>
                        </div>



                        <!-- Buttons -->
                        <div class="flex gap-3 pt-4">
                            <button type="submit" :disabled="form.processing"
                                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-600">
                                {{ form.processing ? 'Creating...' : 'Create Ticket' }}
                            </button>
                            <a :href="ticketsIndex().url"
                                class="rounded-md border border-input bg-background px-4 py-2 text-sm font-medium text-foreground hover:bg-accent hover:text-accent-foreground">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
