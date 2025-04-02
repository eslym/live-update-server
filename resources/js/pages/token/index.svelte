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
    import TableSortCol from '$lib/components/TableSortCol.svelte';
    import { loading } from '$lib/loading.svelte';
    import { Separator } from '$lib/components/ui/separator';
    import { parseAbsoluteToLocal } from '@internationalized/date';
    import { cn, dateTimeFormat } from '$lib/utils';
    import { Trash2Icon } from '@lucide/svelte';
    import { SvelteSet } from 'svelte/reactivity';
    import CreateTokenDialog from '$lib/dialogs/CreateTokenDialog.svelte';
    import { first_layer_dropdown } from '$lib/breadcrumbs';
    import TableFilterDropdown from '$lib/components/TableFilterDropdown.svelte';

    type _keep = [typeof Table, typeof Dialog];

    let {
        tokens,
        recentCreated
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
                    exp: 'expired' | 'active' | 'permanent' | '';
                    sort: string;
                };
            }
        >;
        recentCreated: string | null;
    } = $props();

    let recent_token = recentCreated!;
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

    const delete_dialog = new SvelteSet<number>();
    const deleting = loading.local();

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
        if (recentCreated) {
            router.replace({
                preserveState: true,
                props(p) {
                    delete p.recentlyCreated;
                    return p;
                }
            });
            setTimeout(() => {
                recent_dialog = true;
            }, 100);
        }
    });
</script>

<DashboardMain
    title="API Tokens"
    breadcrumbs={[
        {
            label: 'Tokens',
            dropdown: first_layer_dropdown('tokens')
        }
    ]}
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
        <CreateTokenDialog>
            <Dialog.Trigger
                class={cn(buttonVariants({ variant: 'ghost', size: 'lg' }), 'rounded-none h-full')}
                disabled={loading.value}
            >
                Create Token
            </Dialog.Trigger>
        </CreateTokenDialog>
    {/snippet}
    <Table.Root>
        <Table.Header>
            <Table.Row>
                <Table.Head class="w-0">#</Table.Head>
                <Table.Head>
                    <TableSortCol
                        name="Token Name"
                        column="name"
                        bind:value={form.data.sort}
                        onclick={commit}
                    />
                </Table.Head>
                <Table.Head class="w-0 text-center">
                    <div class="flex flex-row items-center justify-center gap-0.5">
                        <TableSortCol
                            name="Last Used At"
                            column="last_used_at"
                            bind:value={form.data.sort}
                            onclick={commit}
                        />
                        <TableFilterDropdown
                            bind:value={form.data.use}
                            options={[
                                { label: 'All', value: '' },
                                { label: 'Used', value: 'used' },
                                { label: 'Unused', value: 'unused' }
                            ]}
                            onchanged={commit}
                        />
                    </div>
                </Table.Head>
                <Table.Head class="w-0 text-center">
                    <div class="flex flex-row items-center justify-center gap-0.5">
                        <TableSortCol
                            name="Expires At"
                            column="expires_at"
                            bind:value={form.data.sort}
                            onclick={commit}
                        />
                        <TableFilterDropdown
                            bind:value={form.data.exp}
                            options={[
                                { label: 'All', value: '' },
                                { label: 'Expired', value: 'expired' },
                                { label: 'Active', value: 'active' },
                                { label: 'Permanent', value: 'permanent' }
                            ]}
                            onchanged={commit}
                        />
                    </div>
                </Table.Head>
                <Table.Head class="w-0 text-center">
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
            {#each tokens.data as token (token.id)}
                <Table.Row>
                    <Table.Cell class="w-max text-center text-muted-foreground">#</Table.Cell>
                    <Table.Cell>{token.name}</Table.Cell>
                    <Table.Cell class="w-max text-nowrap text-center">
                        {#if token.last_used_at}
                            {@const last_used = parseAbsoluteToLocal(token.last_used_at)}
                            {dateTimeFormat.format(last_used.toDate())}
                        {:else}
                            <span class="text-muted-foreground">(N/A)</span>
                        {/if}
                    </Table.Cell>
                    <Table.Cell class="w-max text-nowrap text-center">
                        {#if token.expires_at}
                            {@const expires = parseAbsoluteToLocal(token.expires_at)}
                            {dateTimeFormat.format(expires.toDate())}
                        {:else}
                            <span class="text-muted-foreground">(N/A)</span>
                        {/if}
                    </Table.Cell>
                    <Table.Cell class="w-max text-nowrap text-center">
                        {@const created = parseAbsoluteToLocal(token.created_at)}
                        {dateTimeFormat.format(created.toDate())}
                    </Table.Cell>
                    <Table.Cell class="w-0">
                        {@render deleteDialog(token.id)}
                    </Table.Cell>
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
        <Input readonly value={recent_token} class="font-mono" />
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

{#snippet deleteDialog(id: number)}
    <Dialog.Root
        bind:open={
            () => delete_dialog.has(id),
            (val) => {
                if (deleting.value) return;
                if (val) {
                    delete_dialog.add(id);
                } else {
                    delete_dialog.delete(id);
                }
            }
        }
    >
        <Dialog.Trigger class={buttonVariants({ variant: 'ghost', size: 'icon' })}>
            <Trash2Icon class="size-4" />
        </Dialog.Trigger>
        <Dialog.Content>
            <Dialog.Header>
                <Dialog.Title>Delete Token</Dialog.Title>
                <Dialog.Description>
                    Are you sure you want to delete this token? This action cannot be undone.
                </Dialog.Description>
            </Dialog.Header>
            <Dialog.Footer>
                <Dialog.Close
                    type="button"
                    class={buttonVariants({ variant: 'secondary' })}
                    disabled={loading.value}
                >
                    Cancel
                </Dialog.Close>
                <Button
                    variant="destructive"
                    onclick={() => {
                        deleting.value = true;
                        router.delete(`/tokens/${id}`, {
                            replace: true,
                            preserveState: true,
                            preserveScroll: true,
                            onFinish() {
                                deleting.value = false;
                                delete_dialog.delete(id);
                            }
                        });
                    }}
                >
                    Delete
                </Button>
            </Dialog.Footer>
        </Dialog.Content>
    </Dialog.Root>
{/snippet}
