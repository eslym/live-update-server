<script lang="ts" module>
    export {default as layout} from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    let {tokens}: {
        tokens: Pagination<{
            id: number,
            name: string,
            expires_at: string,
            created_at: string,
            last_used_at: string | null,
        }>, newToken: string | null
    } = $props();
</script>

<svelte:head>
    <title>Tokens</title>
</svelte:head>
