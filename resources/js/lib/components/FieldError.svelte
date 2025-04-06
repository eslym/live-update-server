<script lang="ts">
    import { slide } from 'svelte/transition';
    import type { ClassValue } from 'clsx';
    import { cn } from '$lib/utils';
    let {
        error,
        class: className
    }: { error: string | undefined | null | string[]; class?: ClassValue } = $props();

    let errors = $derived(error ? (Array.isArray(error) ? error : [error]) : []);
</script>

{#if errors.length}
    <div
        class={cn('text-rose-600 dark:text-rose-400 text-xs pb-2 block px-2', className)}
        transition:slide={{ duration: 150 }}
    >
        {#each errors as error (error)}
            <i transition:slide={{ duration: 150 }} class="block">
                {error}
            </i>
        {/each}
    </div>
{/if}
