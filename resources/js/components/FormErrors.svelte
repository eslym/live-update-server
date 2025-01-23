<script lang="ts">
    import { slide } from 'svelte/transition';

    let { error }: { error?: string | null } = $props();
</script>

{#if error}
    <ul class="form-label list-inside block list-disc" in:slide={{ duration: 150 }}>
        <li class="form-label-alt text-error">{error}</li>
    </ul>
{/if}
