<script lang="ts" module>
    export { default as layout } from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    import DashboardMain from '$lib/components/DashboardMain.svelte';
    import { first_layer_dropdown } from '$lib/breadcrumbs';
    import CreateVersionDialog from '$lib/dialogs/CreateVersionDialog.svelte';
    import { buttonVariants } from '@/lib/components/ui/button';
    import { cn } from '$lib/utils';
    import * as Table from '$lib/components/ui/table';
    import * as Dropdown from '$lib/components/ui/dropdown-menu';
    import { Badge } from '$lib/components/ui/badge';
    import TableSortCol from '@/lib/components/TableSortCol.svelte';
    import { useForm } from '@/inertia';
    import { debounce } from '@/lib/debounce.svelte';
    import { loading } from '@/lib/loading.svelte';
    import { booleans, timestamp } from '@/lib/utils.svelte';
    import { EllipsisIcon } from '@lucide/svelte';
    import DeleteDialog from '@/lib/dialogs/DeleteDialog.svelte';
    import EditVersionDialog from '@/lib/dialogs/EditVersionDialog.svelte';
    import { Separator } from '@/lib/components/ui/separator';

    let {
        project,
        versions,
        channels
    }: {
        project: {
            nanoid: string;
            name: string;
        };
        versions: Pagination<
            {
                id: number;
                nanoid: string;
                name: string;
                channels: (string | null)[];
                reqs: VersionRequirements;
                created_at: string;
            },
            {
                params: {
                    q: string;
                    sort: string;
                };
            }
        >;
        channels: {
            id: number;
            name: string;
        }[];
    } = $props();

    const form = useForm
        .derived(() => ({
            ...versions.meta.params,
            page: versions.meta.current_page
        }))
        .transform((data: any) => {
            if (!data.q) delete data.q;
            if (!data.sort) delete data.sort;
            if (data.page === 1) delete data.page;
            return data;
        });

    const commit = debounce(
        () => {
            if (!form.dirty || form.processing || loading.value) return;
            form.get(`/projects/${project.nanoid}/versions`, {
                replace: true,
                preserveState: true,
                preserveScroll: true,
                only: ['versions']
            });
        },
        () =>
            ([form.dirty, form.processing, Object.values(form.data)] as const)[0] && !loading.value
    );

    const edit_dialog = booleans();
    const delete_dialog = booleans();

    let chs = channels.map((c) => c.name);
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
        },
        {
            label: 'Versions',
            href: `/projects/${project.nanoid}/versions`
        }
    ]}
    title="Versions | Project: {project.name}"
    searchable
    bind:search={form.data.q}
    pagination={versions}
>
    {#snippet actions()}
        <Separator orientation="vertical" />
        <CreateVersionDialog project_id={project.nanoid} channels={chs}>
            {#snippet children(Trigger)}
                <Trigger
                    class={cn(
                        buttonVariants({ variant: 'ghost', size: 'lg' }),
                        'rounded-none h-full'
                    )}
                >
                    New Version
                </Trigger>
            {/snippet}
        </CreateVersionDialog>
    {/snippet}
    <Table.Root>
        <Table.Header>
            <Table.Row>
                <Table.Head class="w-0 text-center">#</Table.Head>
                <Table.Head>
                    <TableSortCol
                        name="Name"
                        column="name"
                        bind:value={form.data.sort}
                        onclick={commit}
                    />
                </Table.Head>
                <Table.Head class="w-0 text-center">Channels</Table.Head>
                <Table.Head class="w-0 text-center">Android</Table.Head>
                <Table.Head class="w-0 text-center">iOS</Table.Head>
                <Table.Head class="w-0 text-center">
                    <TableSortCol
                        name="Created At"
                        column="created_at"
                        bind:value={form.data.sort}
                        onclick={commit}
                    />
                </Table.Head>
                <Table.Head class="w-0 text-center"></Table.Head>
            </Table.Row>
        </Table.Header>
        <Table.Body>
            {#each versions.data as version (version.id)}
                <Table.Row>
                    <Table.Cell class="w-0 text-center text-nowrap">#</Table.Cell>
                    <Table.Cell>{version.name}</Table.Cell>
                    <Table.Cell class="w-0 text-center text-nowrap">
                        <div class="flex flex-row items-center justify-center gap-0.5">
                            {#each version.channels as ch}
                                {@render channel(ch)}
                            {/each}
                        </div>
                    </Table.Cell>
                    <Table.Cell class="w-0 text-center text-nowrap">
                        {@render version_range(version.reqs.android)}
                    </Table.Cell>
                    <Table.Cell class="w-0 text-center text-nowrap">
                        {@render version_range(version.reqs.ios)}
                    </Table.Cell>
                    <Table.Cell class="w-0 text-center text-nowrap">
                        {@render timestamp(version.created_at)}
                    </Table.Cell>
                    <Table.Cell class="w-0 text-center text-nowrap">
                        <Dropdown.Root>
                            <Dropdown.Trigger
                                class={buttonVariants({ variant: 'ghost', size: 'icon' })}
                                disabled={loading.value}
                            >
                                <EllipsisIcon class="size-4" />
                            </Dropdown.Trigger>
                            <Dropdown.Content>
                                <Dropdown.Item onclick={() => (edit_dialog[version.id] = true)}>
                                    Edit
                                </Dropdown.Item>
                                <Dropdown.Item>
                                    {#snippet child({ props })}
                                        <a
                                            {...props}
                                            href={`/api/bundles/${project.nanoid}/${version.nanoid}.zip`}
                                        >
                                            Download
                                        </a>
                                    {/snippet}
                                </Dropdown.Item>
                                <Dropdown.Separator />
                                <Dropdown.Item onclick={() => (delete_dialog[version.id] = true)}>
                                    Delete
                                </Dropdown.Item>
                            </Dropdown.Content>
                        </Dropdown.Root>
                        <EditVersionDialog
                            project_id={project.nanoid}
                            {version}
                            channels={chs}
                            bind:open={edit_dialog[version.id]}
                        />
                        <DeleteDialog
                            action="/projects/{project.nanoid}/versions/{version.nanoid}"
                            title="Delete Version"
                            description="Are you sure you want to delete this version?"
                            bind:open={delete_dialog[version.id]}
                        />
                    </Table.Cell>
                </Table.Row>
            {:else}
                <Table.Row>
                    <Table.Cell colspan={7} class="text-center h-max text-muted-foreground py-8">
                        No versions found
                    </Table.Cell>
                </Table.Row>
            {/each}
        </Table.Body>
    </Table.Root>
</DashboardMain>

{#snippet channel(channel: string | null)}
    <Badge
        variant={channel ? 'secondary' : 'outline'}
        class={channel ? '' : 'text-muted-foreground'}
    >
        {channel ?? '(default)'}
    </Badge>
{/snippet}

{#snippet version_range(range?: VersionRange | null)}
    {#if range}
        {#if range.min && range.max}
            {range.min} - {range.max}
        {:else if range.min}
            ≤ {range.min}
        {:else if range.max}
            ≥ {range.max}
        {:else}
            Any Version
        {/if}
    {:else}
        <span class="text-muted-foreground">N/A</span>
    {/if}
{/snippet}
