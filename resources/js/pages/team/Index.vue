<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TeamMemberCard from '@/components/TeamMemberCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as teamIndex } from '@/routes/team';
import type { User } from '@/types';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Team Members',
        href: teamIndex().url,
    },
];

const props = defineProps<{ auth: { user: User }, teamMembers: User[] }>();

</script>

<template>

    <Head title="Team Members" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <h1 class="text-3xl font-bold">My Team</h1>

            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div v-for="member in teamMembers" :key="member.id ?? member.email ?? member.name"
                                :class="`flex items-center gap-4 rounded-lg border p-4 ${member.id == props.auth.user.id ? 'bg-green-100/50 dark:bg-green-300/20 border-green-300 dark:border-green-700/70' : 'border-sidebar-border dark:border-sidebar-border'}`">
                                <TeamMemberCard :member="member" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
