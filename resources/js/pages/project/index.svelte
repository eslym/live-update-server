<script lang="ts" module>
    export {default as layout} from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    let {projects}: { projects: Pagination<{}> } = $props();
</script>

<svelte:head>
    <title>Projects</title>
</svelte:head>
