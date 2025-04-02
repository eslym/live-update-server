<script lang="ts" module>
    export { default as layout } from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    import * as Table from '$lib/components/ui/table';
    import * as Dialog from '$lib/components/ui/dialog';
    import { onMount } from 'svelte';
    import { router } from '@inertiajs/core';
    import { toast } from 'svelte-sonner';
    import { Button, buttonVariants } from '$lib/components/ui/button';
    import { Input } from '$lib/components/ui/input';
    import { useForm } from '@/inertia';
    import { debounce } from '$lib/debounce.svelte';
    import DashboardMain from '$lib/components/DashboardMain.svelte';
    import TableSortCol from '$lib/dialogs/TableSortCol.svelte';
    import { loading } from '$lib/loading.svelte';
    import { Separator } from '$lib/components/ui/separator';

    type _keep = [typeof Table, typeof Dialog];

    let {
        tokens,
        recentlyCreated
    }: {
        tokens: Pagination<
            {
                id: number;
                name: string;
                expires_at: string | null;
                last_used_at: string | null;
                created_at: string;
            },
            {
                params: {
                    q: string;
                    use: 'used' | 'unused' | '';
                    expired: 'expired' | 'active' | 'permanent' | '';
                    sort: string;
                };
            }
        >;
        recentlyCreated: string | null;
    } = $props();

    let recent_token = recentlyCreated!;
    let recent_dialog = $state(false);
    let copy_loading = $state(false);

    const form = useForm
        .derived(() => ({
            ...tokens.meta.params,
            page: tokens.meta.current_page
        }))
        .transform((d: any) => {
            for (const k of Object.keys(d)) {
                if (d[k] === '') delete d[k];
            }
            if (d.page === 1) delete d.page;
            return d;
        });

    const commit = debounce(
        () => {
            if (!form.dirty || form.processing || loading.value) return;
            form.get('/tokens', {
                replace: true,
                preserveState: true,
                preserveScroll: true
            });
        },
        () =>
            ([form.dirty, form.processing, Object.values(form.data)] as const)[0] && !loading.value
    );

    onMount(() => {
        if (recentlyCreated) {
            recent_dialog = true;
            router.replace({
                preserveState: true,
                props(p) {
                    delete p.recentlyCreated;
                    return p;
                }
            });
        }
    });
</script>

<DashboardMain
    title="API Tokens"
    breadcrumbs={[{ label: 'API Tokens', href: '/tokens' }]}
    bind:search={form.data.q}
    searchable
    pagination={tokens}
    onpageclick={(page) => {
        form.data.page = page;
        commit();
    }}
>
    {#snippet actions()}
        <Separator orientation="vertical" />
        <Button variant="ghost" size="lg" class="rounded-none h-full" disabled={loading.value}>
            Create Token
        </Button>
    {/snippet}
    <Table.Root class="h-full">
        <Table.Header class="sticky top-0">
            <Table.Row>
                <Table.Head class="w-0">
                    <TableSortCol column="id" bind:value={form.data.sort} onclick={commit} />
                </Table.Head>
                <Table.Head>
                    <TableSortCol
                        name="Token Name"
                        column="name"
                        bind:value={form.data.sort}
                        onclick={commit}
                    />
                </Table.Head>
                <Table.Head>
                    <TableSortCol
                        name="Last Used At"
                        column="last_used_at"
                        bind:value={form.data.sort}
                        onclick={commit}
                    />
                </Table.Head>
                <Table.Head>
                    <TableSortCol
                        name="Expires At"
                        column="expires_at"
                        bind:value={form.data.sort}
                        onclick={commit}
                    />
                </Table.Head>
                <Table.Head>
                    <TableSortCol
                        name="Created At"
                        column="created_at"
                        bind:value={form.data.sort}
                        onclick={commit}
                    />
                </Table.Head>
                <Table.Head></Table.Head>
            </Table.Row>
        </Table.Header>
        <Table.Body>
            {#each tokens.data as token}
                <Table.Row>
                    <Table.Cell class="w-0 text-muted-foreground">#</Table.Cell>
                    <Table.Cell>{token.name}</Table.Cell>
                    <Table.Cell>{token.last_used_at}</Table.Cell>
                    <Table.Cell>{token.expires_at}</Table.Cell>
                    <Table.Cell>{token.created_at}</Table.Cell>
                    <Table.Cell class="w-0"></Table.Cell>
                </Table.Row>
            {:else}
                <Table.Row>
                    <Table.Cell colspan={6} class="text-center h-max text-muted-foreground py-8">
                        No tokens found
                    </Table.Cell>
                </Table.Row>
            {/each}
        </Table.Body>
    </Table.Root>
</DashboardMain>

<Dialog.Root bind:open={recent_dialog}>
    <Dialog.Content>
        <Dialog.Header>
            <Dialog.Title>Token Created</Dialog.Title>
            <Dialog.Description>
                Your token has been created successfully. Please copy it and store it in a safe
                place, it will not be shown again.
            </Dialog.Description>
        </Dialog.Header>
        <Input readonly value={recent_token} />
        <Dialog.Footer>
            <Button
                onclick={() => {
                    copy_loading = true;
                    toast.promise(navigator.clipboard.writeText(recent_token), {
                        success: () => {
                            recent_dialog = false;
                            return 'Copied to clipboard';
                        },
                        error: 'Failed to copy to clipboard',
                        finally() {
                            copy_loading = false;
                        }
                    });
                }}
                class={buttonVariants({ variant: 'secondary' })}
                loading={copy_loading}
            >
                Copy & Close
            </Button>
        </Dialog.Footer>
    </Dialog.Content>
</Dialog.Root>
