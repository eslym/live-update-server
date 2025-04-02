<script lang="ts" module>
    export { default as layout } from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    import { first_layer_dropdown } from '$lib/breadcrumbs';
    import DashboardMain from '$lib/components/DashboardMain.svelte';
    import { Separator } from '$lib/components/ui/separator';
    import CreateProjectDialog from '$lib/dialogs/CreateProjectDialog.svelte';
    import { cn, dateTimeFormat } from '$lib/utils';
    import { Button, buttonVariants } from '$lib/components/ui/button';
    import { loading } from '$lib/loading.svelte';
    import { useForm } from '@/inertia';
    import { debounce } from '$lib/debounce.svelte';
    import * as Table from '$lib/components/ui/table';
    import TableSortCol from '$lib/components/TableSortCol.svelte';
    import { parseAbsoluteToLocal } from '@internationalized/date';
    import { ExternalLinkIcon } from '@lucide/svelte';

    type _keep = [typeof Table];

    let {
        projects
    }: {
        projects: Pagination<
            {
                id: number;
                nanoid: string;
                name: string;
                created_at: string;
                versions: [
                    | {
                          id: number;
                          nanoid: string;
                          name: string;
                      }
                    | undefined
                ];
            },
            {
                params: {
                    q: string;
                    sort: string;
                };
            }
        >;
    } = $props();

    const form = useForm
        .derived(() => ({
            ...projects.meta.params,
            page: projects.meta.current_page
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
            form.get('/projects', {
                replace: true,
                preserveState: true,
                preserveScroll: true,
                only: ['projects']
            });
        },
        () =>
            ([form.dirty, form.processing, Object.values(form.data)] as const)[0] && !loading.value
    );
</script>

<DashboardMain
    breadcrumbs={[
        {
            label: 'Projects',
            dropdown: first_layer_dropdown('projects')
        }
    ]}
    title="Projects"
    bind:search={form.data.q}
    searchable
    pagination={projects}
    onpageclick={(page) => {
        form.data.page = page;
        commit();
    }}
>
    {#snippet actions()}
        <Separator orientation="vertical" />
        <CreateProjectDialog>
            {#snippet children(Trigger)}
                <Trigger
                    class={cn(buttonVariants({ variant: 'ghost' }), 'rounded-none h-full')}
                    disabled={loading.value}
                >
                    Create Project
                </Trigger>
            {/snippet}
        </CreateProjectDialog>
    {/snippet}
    <Table.Root>
        <Table.Header>
            <Table.Row>
                <Table.Head class="w-0">#</Table.Head>
                <Table.Head>
                    <TableSortCol
                        name="Name"
                        column="name"
                        bind:value={form.data.sort}
                        onclick={commit}
                    />
                </Table.Head>
                <Table.Head class="w-0 text-center text-nowrap">Latest Version</Table.Head>
                <Table.Head class="w-0 text-center text-nowrap">
                    <TableSortCol
                        name="Created At"
                        column="created_at"
                        bind:value={form.data.sort}
                        onclick={commit}
                    />
                </Table.Head>
                <Table.Head class="w-0"></Table.Head>
            </Table.Row>
        </Table.Header>
        <Table.Body>
            {#each projects.data as project (project.id)}
                <Table.Row>
                    <Table.Cell class="w-0">#</Table.Cell>
                    <Table.Cell>
                        {project.name}
                    </Table.Cell>
                    <Table.Cell class="w-0 text-center">
                        {#if project.versions[0]}
                            {project.versions[0].name}
                        {:else}
                            <span class="text-muted-foreground">(N/A)</span>
                        {/if}
                    </Table.Cell>
                    <Table.Cell class="w-0 text-center text-nowrap">
                        {@const created = parseAbsoluteToLocal(project.created_at)}
                        {dateTimeFormat.format(created.toDate())}
                    </Table.Cell>
                    <Table.Cell class="w-0 text-nowrap">
                        <Button href="/projects/{project.nanoid}" variant="ghost" size="icon">
                            <ExternalLinkIcon class="size-4" />
                        </Button>
                    </Table.Cell>
                </Table.Row>
            {/each}
        </Table.Body>
    </Table.Root>
</DashboardMain>
