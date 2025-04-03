<script lang="ts" module>
    export { default as layout } from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    import { first_layer_dropdown } from '$lib/breadcrumbs';
    import DashboardMain from '$lib/components/DashboardMain.svelte';
    import { useForm } from '@/inertia';
    import { loading } from '$lib/loading.svelte';
    import { Label } from '$lib/components/ui/label';
    import { Input } from '$lib/components/ui/input';
    import FieldError from '$lib/components/FieldError.svelte';
    import { Textarea } from '$lib/components/ui/textarea';
    import { Button } from '$lib/components/ui/button';

    let {
        project
    }: {
        project: {
            id: number;
            nanoid: string;
            name: string;
            description: string;
            public_key: string;
        };
    } = $props();

    const form = useForm.derived(() => ({
        name: project.name,
        description: project.description
    }));

    const processing = loading.derived(() => form.processing);
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
>
    <div class="p-8">
        <form
            class="grid grid-cols-[auto,1fr] gap-x-4 gap-y-2 max-w-lg"
            novalidate
            method="post"
            action="/projects/{project.nanoid}"
            use:form.action
        >
            <div class="grid col-span-2 grid-cols-subgrid gap-y-1.5">
                <Label for="id" class="flex items-center justify-end">ID</Label>
                <Input type="text" id="id" class="font-mono" value={project.nanoid} readonly />
            </div>
            <div class="grid col-span-2 grid-cols-subgrid gap-y-1.5">
                <Label for="name" class="flex items-center justify-end">Name</Label>
                <Input
                    type="text"
                    id="name"
                    bind:value={form.data.name}
                    placeholder={project.name}
                    disabled={loading.value}
                />
                <FieldError error={form.errors.name} class="col-[2]" />
            </div>
            <div class="grid col-span-2 grid-cols-subgrid gap-y-1.5">
                <Label for="description" class="flex justify-end py-2">Description</Label>
                <Textarea
                    id="description"
                    bind:value={form.data.description}
                    placeholder={project.description}
                    disabled={loading.value}
                    rows={5}
                />
                <FieldError error={form.errors.description} class="col-[2]" />
            </div>
            <div class="grid col-span-2 grid-cols-subgrid gap-y-1.5">
                <Label for="public_key" class="flex justify-end py-2">Public Key</Label>
                <Textarea
                    id="public_key"
                    value={project.public_key}
                    class="font-mono"
                    readonly
                    rows={10}
                />
            </div>
            <div class="grid grid-cols-2 col-[2] gap-2 mt-12">
                <Button type="submit" loading={processing.value} disabled={loading.or(!form.dirty)}>
                    Save
                </Button>
            </div>
        </form>
    </div>
</DashboardMain>
