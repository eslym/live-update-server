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
            if (!form.dirty || form.processing) return;
            form.get('/tokens', {
                replace: true,
                preserveState: true,
                preserveScroll: true
            });
        },
        () => ([form.dirty, form.processing, Object.values(form.data)] as const)[0]
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
>
    <Table.Root>
        <Table.Header>
            <Table.Row>
                <Table.Head>#</Table.Head>
                <Table.Head>Token Name</Table.Head>
                <Table.Head>Created At</Table.Head>
                <Table.Head>Last Used</Table.Head>
                <Table.Head>Expires At</Table.Head>
                <Table.Head></Table.Head>
            </Table.Row>
        </Table.Header>
        <Table.Body></Table.Body>
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
