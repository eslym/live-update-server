<script lang="ts">
    import { slide } from 'svelte/transition';
    let { error }: { error: string | undefined | null } = $props();
</script>

{#if error}
    <i transition:slide={{ duration: 150 }} class="text-rose-600 dark:text-rose-400 text-xs mb-2">
        {error}
    </i>
{/if}
