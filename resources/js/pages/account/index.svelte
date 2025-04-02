<script lang="ts" module>
    export { default as layout } from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    import * as Table from '$lib/components/ui/table';
    import * as Dialog from '$lib/components/ui/dialog';
    import * as Dropdown from '$lib/components/ui/dropdown-menu';
    import DashboardMain from '$lib/components/DashboardMain.svelte';
    import { first_layer_dropdown } from '$lib/breadcrumbs';
    import { useForm } from '@/inertia';
    import { debounce } from '$lib/debounce.svelte';
    import { loading } from '$lib/loading.svelte';
    import CreateUserDialog from '$lib/dialogs/CreateUserDialog.svelte';
    import { cn, dateTimeFormat } from '$lib/utils';
    import { Button, buttonVariants } from '$lib/components/ui/button';
    import { Separator } from '$lib/components/ui/separator';
    import { parseAbsoluteToLocal } from '@internationalized/date';
    import TableSortCol from '$lib/components/TableSortCol.svelte';
    import { Badge } from '$lib/components/ui/badge/index.js';
    import { EllipsisIcon } from '@lucide/svelte';
    import UpdateUserDialog from '$lib/dialogs/UpdateUserDialog.svelte';
    import { SvelteSet } from 'svelte/reactivity';
    import { router } from '@inertiajs/core';

    type _keep = [typeof Dialog, typeof Table, typeof Dropdown];

    let {
        accounts,
        can_create
    }: {
        accounts: Pagination<
            {
                id: number;
                nanoid: string;
                name: string;
                email: string;
                created_at: string;
                is_mutable: boolean;
                is_2fa_enabled: boolean;
            },
            {
                params: {
                    q: string;
                    sort: string;
                };
            }
        >;
        can_create: boolean;
    } = $props();

    const form = useForm
        .derived(() => ({
            ...accounts.meta.params,
            page: accounts.meta.current_page
        }))
        .transform((data: any) => {
            if (!data.q) delete data.q;
            if (!data.sort) delete data.sort;
            if (data.page === 1) delete data.page;
            return data;
        });

    const deleting = loading.local();

    const edit_dialog = new SvelteSet<number>();
    const delete_dialog = new SvelteSet<number>();

    const commit = debounce(
        () => {
            if (!form.dirty || form.processing || loading.value) return;
            form.get('/accounts', {
                replace: true,
                preserveState: true,
                preserveScroll: true,
                only: ['accounts']
            });
        },
        () =>
            ([form.dirty, form.processing, Object.values(form.data)] as const)[0] && !loading.value
    );
</script>

<DashboardMain
    breadcrumbs={[
        {
            label: 'Accounts',
            dropdown: first_layer_dropdown('accounts')
        }
    ]}
    title="Accounts"
    searchable
    bind:search={form.data.q}
    pagination={accounts}
    onpageclick={(page) => {
        form.data.page = page;
        commit();
    }}
>
    {#snippet actions()}
        {#if can_create}
            <Separator orientation="vertical" />
            <CreateUserDialog>
                <Dialog.Trigger
                    class={cn(buttonVariants({ variant: 'ghost' }), 'rounded-none h-full')}
                    disabled={loading.value}
                >
                    Create Account
                </Dialog.Trigger>
            </CreateUserDialog>
        {/if}
    {/snippet}
    <Table.Root>
        <Table.Header>
            <Table.Row>
                <Table.Head class="w-0 min-w-max">#</Table.Head>
                <Table.Head>
                    <TableSortCol
                        name="Name"
                        column="name"
                        bind:value={form.data.sort}
                        onclick={commit}
                    />
                </Table.Head>
                <Table.Head>
                    <TableSortCol
                        name="Email"
                        column="email"
                        bind:value={form.data.sort}
                        onclick={commit}
                    />
                </Table.Head>
                <Table.Head class="w-0 min-w-max text-center">2FA</Table.Head>
                <Table.Head class="w-0 min-w-max text-center">
                    <TableSortCol
                        name="Created At"
                        column="created_at"
                        bind:value={form.data.sort}
                        onclick={commit}
                    />
                </Table.Head>
                <Table.Head class="w-0 min-w-max"></Table.Head>
            </Table.Row>
        </Table.Header>
        <Table.Body>
            {#each accounts.data as account (account.id)}
                <Table.Row>
                    <Table.Cell class="w-0 min-w-max">#</Table.Cell>
                    <Table.Cell>{account.name}</Table.Cell>
                    <Table.Cell>{account.email}</Table.Cell>
                    <Table.Head class="w-0 min-w-max text-center">
                        <Badge variant={account.is_2fa_enabled ? 'default' : 'secondary'}>
                            {account.is_2fa_enabled ? 'Enabled' : 'Disabled'}
                        </Badge>
                    </Table.Head>
                    <Table.Cell class="text-nowrap text-center">
                        {@const created = parseAbsoluteToLocal(account.created_at)}
                        {dateTimeFormat.format(created.toDate())}
                    </Table.Cell>
                    {#if can_create}
                        <Table.Cell>
                            <Dropdown.Root>
                                <Dropdown.Trigger
                                    disabled={!account.is_mutable}
                                    class={buttonVariants({ variant: 'ghost', size: 'icon' })}
                                >
                                    <EllipsisIcon class="size-4" />
                                </Dropdown.Trigger>
                                <Dropdown.Content>
                                    <Dropdown.Item onclick={() => edit_dialog.add(account.id)}>
                                        Edit
                                    </Dropdown.Item>
                                    <Dropdown.Item onclick={() => delete_dialog.add(account.id)}>
                                        Delete
                                    </Dropdown.Item>
                                </Dropdown.Content>
                            </Dropdown.Root>
                            <UpdateUserDialog
                                {account}
                                bind:open={
                                    () => edit_dialog.has(account.id),
                                    (val) => {
                                        if (val) edit_dialog.add(account.id);
                                        else edit_dialog.delete(account.id);
                                    }
                                }
                            />
                            {@render deleteDialog(account)}
                        </Table.Cell>
                    {/if}
                </Table.Row>
            {:else}
                <Table.Row>
                    <Table.Cell
                        colspan={can_create ? 6 : 5}
                        class="text-center h-max text-muted-foreground py-8"
                    >
                        No accounts found
                    </Table.Cell>
                </Table.Row>
            {/each}
        </Table.Body>
    </Table.Root>
</DashboardMain>

{#snippet deleteDialog(account: (typeof accounts.data)[0])}
    <Dialog.Root
        bind:open={
            () => delete_dialog.has(account.id),
            (val) => {
                if (deleting.value) return;
                if (val) {
                    delete_dialog.add(account.id);
                } else {
                    delete_dialog.delete(account.id);
                }
            }
        }
    >
        <Dialog.Content>
            <Dialog.Header>
                <Dialog.Title>Delete User: {account.name}</Dialog.Title>
                <Dialog.Description>
                    Are you sure you want to delete this user? This action cannot be undone.
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
                        router.delete(`/accounts/${account.nanoid}`, {
                            replace: true,
                            preserveState: true,
                            preserveScroll: true,
                            onFinish() {
                                deleting.value = false;
                                delete_dialog.delete(account.id);
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
