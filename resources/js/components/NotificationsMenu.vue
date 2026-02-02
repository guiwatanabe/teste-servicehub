<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import { Bell } from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuTrigger,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuItem,
} from '@/components/ui/dropdown-menu';
import { SidebarMenuItem, SidebarMenuButton } from '@/components/ui/sidebar';
import { notificationRead } from '@/services/notifications.service';
import type { Notification } from '@/types/notification';
import { show as showTicket } from '@/routes/tickets';

const page = usePage();
const notifications =
    (page.props.notifications as Notification[] | undefined) ?? [];

function formatDate(dateString: string) {
    const date = new Date(dateString);
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
}

function markAsReadAndGo(notification: Notification) {
    notificationRead(notification.id).then(() => {
        router.visit(showTicket(notification.data.ticket_id).url);
    });
}
</script>

<template>
    <SidebarMenuItem>
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <SidebarMenuButton size="lg" class="relative w-full">
                    <Bell class="h-5 w-5" /> Notifications
                    <span
                        v-if="notifications.length"
                        class="inline-flex h-2 w-2 rounded-full bg-red-500"
                    ></span>
                </SidebarMenuButton>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-80">
                <DropdownMenuLabel>Notifications</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <template v-if="notifications.length">
                    <DropdownMenuItem
                        v-for="notification in notifications"
                        :key="notification.id"
                        as-child
                    >
                        <div class="flex flex-col items-start">
                            <span class="text-sm">{{
                                notification.data.message
                            }}</span>
                            <span class="text-xs text-muted-foreground">
                                {{ formatDate(notification.created_at) }}
                            </span>
                            <button
                                v-if="notification.data.ticket_id"
                                @click="markAsReadAndGo(notification)"
                                class="mt-1 text-xs text-primary underline"
                            >
                                View
                            </button>
                        </div>
                    </DropdownMenuItem>
                </template>
                <template v-else>
                    <DropdownMenuItem as-child>
                        <span class="text-sm text-muted-foreground"
                            >No notifications.</span
                        >
                    </DropdownMenuItem>
                </template>
            </DropdownMenuContent>
        </DropdownMenu>
    </SidebarMenuItem>
</template>
