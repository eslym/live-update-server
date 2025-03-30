<script lang="ts">
    import type { Snippet } from 'svelte';

    let { children, illustration = undefined }: { children: Snippet; illustration?: string } =
        $props();
</script>

<div class="min-h-dvh grid max-lg:grid-cols-1 grid-cols-2 mx-auto max-w-screen-xl">
    <div
        class="relative overflow-hidden max-h-dvh max-lg:hidden flex items-center justify-center select-none"
    >
        {#if illustration}
            <img src={illustration} alt="Illustration" class="size-80 opacity-60" />
        {/if}
    </div>
    <div class="px-12 py-12 max-sm:px-6 max-md:px-8 flex flex-col items-center justify-center">
        {@render children()}
    </div>
</div>
