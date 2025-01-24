<script lang="ts">
    import { inertia } from '@inertiajs/svelte';

    let {
        links,
        url = (url) => url
    }: {
        links: PaginationLink[];
        url?: (url: URL) => URL;
    } = $props();
</script>

<div class="btn-group">
    {#each links as link}
        {#if link.url}
            <a
                href={url(new URL(link.url, window.location.href)).href}
                class="btn"
                class:btn-primary={link.active}
                use:inertia={{ preserveState: true, preserveScroll: true, replace: true }}
                >{@html link.label}</a
            >
        {:else}
            <button class="btn" disabled>{@html link.label}</button>
        {/if}
    {/each}
</div>
