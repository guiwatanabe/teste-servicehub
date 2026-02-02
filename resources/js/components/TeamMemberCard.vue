<script setup lang="ts">
import type { User } from '@/types';

defineProps<{ member: User; }>();
</script>

<template>
	<div class="flex items-center gap-3">
		<div
			class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-muted aspect-square">
			<svg class="h-6 w-6 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor"
				stroke-width="1.5" aria-hidden="true">
				<circle cx="12" cy="8" r="3.5" />
				<path d="M4 20c1.5-4 6.5-6 8-6s6.5 2 8 6" />
			</svg>
		</div>
		<div class="min-w-0 space-y-0.5">
			<div class="truncate text-sm font-semibold">
				{{ member.name }}
			</div>
			<div class="truncate text-xs text-muted-foreground">
				{{ member?.user_profile?.role ?? 'Employee' }}
			</div>
			<div class="truncate text-xs text-muted-foreground">
				{{ member.email }}
			</div>
			<div v-if="member?.user_profile?.phone" class="truncate text-xs text-muted-foreground">
				{{ member.user_profile.phone }}
			</div>
		</div>
	</div>
</template>
