<script lang="ts" module>
    export { default as layout } from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    import { first_layer_dropdown } from '$lib/breadcrumbs';
    import DashboardMain from '$lib/components/DashboardMain.svelte';

    let {
        project
    }: {
        project: {
            id: number;
            nanoid: string;
            name: string;
        };
    } = $props();
</script>

<DashboardMain
    breadcrumbs={[
        {
            label: 'Projects',
            dropdown: first_layer_dropdown('projects')
        },
        {
            label: project.name
        },
        {
            label: 'Info',
            dropdown: [
                {
                    label: 'Info',
                    href: `/projects/${project.nanoid}`,
                    active: true
                },
                {
                    label: 'Versions',
                    href: `/projects/${project.nanoid}/versions`
                }
            ]
        }
    ]}
    title="Project: {project.name}"
></DashboardMain>
