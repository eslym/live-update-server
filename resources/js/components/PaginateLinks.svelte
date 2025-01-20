<script lang="ts">
    import {inertia} from "@inertiajs/svelte";

    let {links}: { links: PaginationLink[] } = $props();
</script>

<div class="btn-group">
    {#each links as link}
        {#if link.url}
            <a
                href={link.url}
                class="btn"
                class:btn-primary={link.active}
                use:inertia={{ preserveScroll: true, replace: true }}
            >{@html link.label}</a>
        {:else}
            <button class="btn" disabled>{@html link.label}</button>
        {/if}
    {/each}
</div>
