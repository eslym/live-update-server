<script lang="ts">
    import { slide } from 'svelte/transition';
    import type { ClassValue } from 'clsx';
    import { cn } from '$lib/utils';
    let { error, class: className }: { error: string | undefined | null; class?: ClassValue } =
        $props();
</script>

{#if error}
    <i
        transition:slide={{ duration: 150 }}
        class={cn('text-rose-600 dark:text-rose-400 text-xs pb-2 block px-2', className)}
    >
        {error}
    </i>
{/if}
