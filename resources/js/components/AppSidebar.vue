<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ClipboardList,
    FolderGit2,
    FolderKanban,
    LayoutGrid,
    Users,
} from 'lucide-vue-next';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';
import NotificationsMenu from './NotificationsMenu.vue';
import { dashboard } from '@/routes';
import { index as projectsIndex } from '@/routes/projects';
import { index as teamIndex } from '@/routes/team';
import { index as ticketsIndex } from '@/routes/tickets';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Tickets',
        href: ticketsIndex(),
        icon: FolderKanban,
    },
    {
        title: 'Projects',
        href: projectsIndex(),
        icon: ClipboardList,
    },
    {
        title: 'Team',
        href: teamIndex(),
        icon: Users,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github',
        href: 'https://github.com/guiwatanabe/teste-servicehub',
        icon: FolderGit2,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>

                <NotificationsMenu />
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
