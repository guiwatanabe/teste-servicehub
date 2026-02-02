<script setup lang="ts">
import { computed } from 'vue';
import type { TicketDetail } from '@/types/ticket';

const props = defineProps<{ ticketDetail: TicketDetail }>();

const renderValue = (value: unknown): string =>
    typeof value === 'object' ? JSON.stringify(value, null, 2) : String(value);

const parsedContent = computed(() => {
    if (
        props.ticketDetail.details &&
        props.ticketDetail.details.type === 'json' &&
        typeof props.ticketDetail.details.content === 'string'
    ) {
        try {
            return JSON.parse(props.ticketDetail.details.content);
        } catch {
            return props.ticketDetail.details.content;
        }
    }
    return props.ticketDetail.details?.content;
});
</script>

<template>
    <div v-if="ticketDetail.details" class="border border-border p-2">
        <div v-if="ticketDetail.details.type === 'text'">
            <p class="text-sm leading-relaxed text-foreground">
                {{ ticketDetail.details.content }}
            </p>
        </div>
        <div v-else>
            <ul
                v-if="parsedContent && typeof parsedContent === 'object'"
                class="space-y-2 text-sm"
            >
                <li
                    v-for="(value, key) in parsedContent"
                    :key="key"
                    class="rounded-md border border-border/60 bg-muted/30 p-2"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="w-full rounded bg-muted px-2 py-0.5 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            {{ key }}
                        </span>
                        <span
                            v-if="Array.isArray(value)"
                            class="text-foreground"
                        >
                            <ul class="mt-2 space-y-1">
                                <li
                                    v-for="(item, index) in value"
                                    :key="index"
                                    class="text-foreground"
                                >
                                    {{ renderValue(item) }}
                                </li>
                            </ul>
                        </span>
                        <span
                            v-else-if="typeof value === 'object'"
                            class="w-full"
                        >
                            <pre
                                class="mt-1 rounded bg-background/60 p-2 text-xs whitespace-pre-wrap text-foreground"
                                >{{ JSON.stringify(value, null, 2) }}</pre
                            >
                        </span>
                        <span v-else class="text-foreground">
                            {{ renderValue(value) }}
                        </span>
                    </div>
                </li>
            </ul>
            <div v-else class="text-sm text-muted-foreground">
                {{ parsedContent }}
            </div>
        </div>
    </div>
    <div v-else>
        <span class="text-sm text-muted-foreground">No details available.</span>
    </div>
</template>
