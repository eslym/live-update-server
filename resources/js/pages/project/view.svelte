<script lang="ts" module>
    export { default as layout } from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    import { first_layer_dropdown } from '$lib/breadcrumbs';
    import DashboardMain from '$lib/components/DashboardMain.svelte';
    import { useForm, router } from '@/inertia';
    import { loading } from '$lib/loading.svelte';
    import { Label } from '$lib/components/ui/label';
    import { Input } from '$lib/components/ui/input';
    import FieldError from '$lib/components/FieldError.svelte';
    import { Textarea } from '$lib/components/ui/textarea';
    import { Button } from '$lib/components/ui/button';
    import * as Dialog from '$lib/components/ui/dialog';
    import { buttonVariants } from '$lib/components/ui/button';
    import DeleteDialog from '@/lib/dialogs/DeleteDialog.svelte';
    import CreateChannelDialog from '@/lib/dialogs/CreateChannelDialog.svelte';
    import { PlusIcon, TrashIcon } from '@lucide/svelte';
    import { ScrollArea } from '$lib/components/ui/scroll-area';

    let {
        versions,
        channels,
        project
    }: {
        versions: number;
        channels: {
            id: number;
            name: string;
        }[];
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

    let delete_dialog = $state(false);
</script>

<DashboardMain
    breadcrumbs={[
        {
            label: 'Projects',
            dropdown: first_layer_dropdown('projects')
        },
        {
            label: project.name,
            href: `/projects/${project.nanoid}`
        }
    ]}
    title="Project: {project.name}"
>
    <div class="p-8 flex flex-row gap-x-14 gap-y-8 flex-wrap">
        <form
            class="grid grid-cols-[auto,1fr] gap-x-4 gap-y-2 max-w-lg w-full"
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
                <Label for="endpoint" class="flex items-center justify-end">Query Endpoint</Label>
                <Input
                    type="text"
                    id="endpoint"
                    class="font-mono"
                    value="{window.location.origin}/api/bundles/{project.nanoid}"
                    readonly
                />
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
                    class="font-mono text-nowrap"
                    readonly
                    rows={10}
                />
            </div>
            <div class="grid grid-cols-2 col-[2] gap-2 mt-12">
                <Button type="submit" loading={processing.value} disabled={loading.or(!form.dirty)}>
                    Save
                </Button>
                <DeleteDialog
                    bind:open={delete_dialog}
                    action="/projects/{project.nanoid}"
                    title="Delete Project"
                    description="Are you sure you want to delete this project? This action cannot be undone."
                >
                    <Dialog.Trigger
                        type="button"
                        class={buttonVariants({ variant: 'destructive' })}
                        disabled={loading.or(versions > 0 || form.dirty)}
                    >
                        Delete
                    </Dialog.Trigger>
                </DeleteDialog>
            </div>
        </form>
        <div class="max-w-xs w-full">
            <div class="flex flex-row justify-between items-center">
                <h2 class="font-semibold">
                    Channels
                    <CreateChannelDialog project_id={project.nanoid}>
                        <Dialog.Trigger
                            type="button"
                            class={buttonVariants({ variant: 'secondary', size: 'icon' })}
                            disabled={loading.value}
                        >
                            <PlusIcon class="size-3" />
                        </Dialog.Trigger>
                    </CreateChannelDialog>
                </h2>
                <Button
                    type="button"
                    href={`/projects/${project.nanoid}/versions`}
                    variant="ghost"
                    disabled={loading.value}
                >
                    View Versions ({versions})
                </Button>
            </div>
            <ScrollArea class="border rounded border-border p-2 h-[300px] mt-4">
                <div class="flex flex-col gap-2">
                    <div class="text-muted-foreground">(default)</div>
                    {#each channels as channel (channel.id)}
                        <div class="flex flex-row justify-between items-center">
                            <span>{channel.name}</span>
                            <DeleteDialog
                                action={`/projects/${project.nanoid}/channels/${channel.name}`}
                                title="Delete Channel"
                                description="Are you sure you want to delete this channel? This action cannot be undone."
                            >
                                <Dialog.Trigger
                                    type="button"
                                    class={buttonVariants({ variant: 'ghost', size: 'icon' })}
                                    disabled={loading.value}
                                >
                                    <TrashIcon class="size-3" />
                                </Dialog.Trigger>
                            </DeleteDialog>
                        </div>
                    {/each}
                </div>
            </ScrollArea>
        </div>
    </div>
</DashboardMain>
