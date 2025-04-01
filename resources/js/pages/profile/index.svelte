<script lang="ts" module>
    export { default as layout } from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    import { onMount } from 'svelte';
    import { router, useForm } from '@/inertia';
    import * as Dialog from '$lib/components/ui/dialog';
    import { Button, buttonVariants } from '@/lib/components/ui/button';
    import { toast } from 'svelte-sonner';
    import { Badge } from '$lib/components/ui/badge';
    import { loading } from '$lib/loading.svelte';
    import { Label } from '$lib/components/ui/label';
    import { Input } from '$lib/components/ui/input';
    import FieldError from '$lib/components/FieldError.svelte';
    import Remove2FADialog from '$lib/dialogs/Remove2FADialog.svelte';
    import DashboardMain from '$lib/components/DashboardMain.svelte';

    type _keep = [typeof Dialog];

    let { user, recovery_codes }: { user: User; recovery_codes: string[] | null } = $props();

    let display_codes = $state(recovery_codes ?? []);
    let codes_dialog = $state(false);

    let copy_loading = $state(false);

    const form = useForm(
        {
            name: user.name,
            email: user.email,
            current_password: '',
            password: '',
            password_confirmation: ''
        },
        'profile-form'
    )
        .transform((data) => ({
            ...data,
            name: data.name === user.name ? null : data.name,
            email: data.email === user.email ? null : data.email
        }))
        .remember((data) => {
            data.current_password = '';
            data.password = '';
            data.password_confirmation = '';
            return data;
        });

    const processing = loading.derived(() => form.processing);

    onMount(() => {
        if (recovery_codes) {
            codes_dialog = true;
            router.replace({
                preserveState: true,
                props(props) {
                    delete props.recovery_codes;
                    return props;
                }
            });
        }
    });
</script>

<DashboardMain title="Profile" breadcrumbs={[{ label: 'Profile', href: '/profile' }]}>
    <div class="p-8">
        <form
            class="grid grid-cols-[auto,1fr] gap-x-4 gap-y-2 max-w-lg"
            novalidate
            method="post"
            action="/profile"
            use:form.action
        >
            <div class="grid col-span-2 grid-cols-subgrid gap-y-1.5">
                <Label for="name" class="flex items-center justify-end">Name</Label>
                <Input
                    type="name"
                    id="name"
                    bind:value={form.data.name}
                    placeholder={user.name}
                    disabled={loading.value}
                />
                <FieldError error={form.errors.name} class="col-[2]" />
            </div>
            <div class="grid col-span-2 grid-cols-subgrid gap-y-1.5">
                <Label for="email" class="flex items-center justify-end">Email</Label>
                <Input
                    type="email"
                    id="email"
                    bind:value={form.data.email}
                    placeholder={user.email}
                    disabled={loading.value}
                />
                <FieldError error={form.errors.email} class="col-[2]" />
            </div>
            <div class="grid col-span-2 grid-cols-subgrid gap-y-1.5 mt-6">
                <Label for="password" class="flex items-center justify-end">New Password</Label>
                <Input
                    type="password"
                    id="password"
                    bind:value={form.data.password}
                    disabled={loading.value}
                    placeholder="Leave blank to keep current password"
                />
                <FieldError error={form.errors.password} class="col-[2]" />
            </div>
            <div class="grid col-span-2 grid-cols-subgrid gap-y-1.5">
                <Label for="password_confirmation" class="flex items-center justify-end"
                    >Confirm Password</Label
                >
                <Input
                    type="password"
                    id="password_confirmation"
                    bind:value={form.data.password_confirmation}
                    disabled={loading.value}
                />
                <FieldError error={form.errors.password_confirmation} class="col-[2]" />
            </div>
            <div class="grid col-span-2 grid-cols-subgrid gap-y-1.5 mt-6">
                <Label for="current_password" class="flex items-center justify-end"
                    >Current Password</Label
                >
                <Input
                    type="password"
                    id="current_password"
                    bind:value={form.data.current_password}
                    disabled={loading.value}
                    placeholder="When changing email or password"
                />
                <FieldError error={form.errors.current_password} class="col-[2]" />
            </div>
            <div class="grid grid-cols-2 col-[2] gap-2 mt-12">
                <Button type="submit" loading={processing.value} disabled={loading.or(!form.dirty)}>
                    Save
                </Button>
                {#if user['2fa_enabled']}
                    <Remove2FADialog>
                        <Dialog.Trigger
                            type="button"
                            class={buttonVariants({ variant: 'destructive' })}
                        >
                            Remove 2FA
                        </Dialog.Trigger>
                    </Remove2FADialog>
                {:else}
                    <Button href="/2fa/setup" variant="secondary" disabled={loading.value}>
                        Setup 2FA
                    </Button>
                {/if}
            </div>
        </form>
    </div>
</DashboardMain>

<Dialog.Root bind:open={codes_dialog}>
    <Dialog.Content>
        <Dialog.Header>
            <Dialog.Title>Recovery Codes</Dialog.Title>
            <Dialog.Description>
                These codes can be used to access your account if you lose access to your 2FA
                device, or if you are unable to use it for any reason. Please keep them in a safe
                place.
            </Dialog.Description>
        </Dialog.Header>
        <div
            class="grid grid-cols-[repeat(auto-fill,6.25rem)] justify-between gap-x-4 gap-y-2 flex-wrap"
        >
            {#each display_codes as code}
                <div class="flex flex-row justify-center">
                    <Badge class="font-normal font-mono select-text">{code}</Badge>
                </div>
            {/each}
        </div>
        <Dialog.Footer>
            <Button
                onclick={() => {
                    copy_loading = true;
                    toast.promise(navigator.clipboard.writeText(display_codes.join('\n')), {
                        success: () => {
                            codes_dialog = false;
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
