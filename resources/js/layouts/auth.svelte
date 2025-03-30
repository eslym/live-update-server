<script lang="ts">
    import type { Snippet } from 'svelte';

    let { children, illustration = undefined }: { children: Snippet; illustration?: string } =
        $props();
</script>

<div class="min-h-dvh grid max-xl:grid-cols-1 grid-cols-2">
    <div
        class="relative overflow-hidden max-h-dvh max-xl:hidden flex items-center justify-center select-none"
    >
        {#if illustration}
            <img src={illustration} alt="Illustration" class="size-80 opacity-60" />
        {/if}
    </div>
    <div class="px-12 py-12 max-sm:px-6 max-md:px-8 flex flex-col items-center justify-center">
        {@render children()}
    </div>
</div>
