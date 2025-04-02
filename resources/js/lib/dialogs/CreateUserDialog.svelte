<script lang="ts">
    import * as Dialog from '$lib/components/ui/dialog';
    import type { Snippet } from 'svelte';
    import { useForm } from '@/inertia';
    import { loading } from '$lib/loading.svelte';
    import { Button, buttonVariants } from '$lib/components/ui/button';
    import { Label } from '$lib/components/ui/label';
    import { Input } from '$lib/components/ui/input';
    import FieldError from '$lib/components/FieldError.svelte';

    let {
        open = $bindable(false),
        children = undefined
    }: {
        open?: boolean;
        children?: Snippet<[Trigger: typeof Dialog.Trigger]>;
    } = $props();

    const id = $props.id();

    const form = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
    });

    const processing = loading.derived(() => form.processing);
</script>

<Dialog.Root
    bind:open={
        () => open,
        (val) => {
            if (!processing.value) open = val;
        }
    }
    onOpenChange={(val) => {
        if (val) {
            form.reset();
            form.errors = {};
        }
    }}
>
    {@render children?.(Dialog.Trigger)}
    <Dialog.Content>
        <form class="contents" action="/accounts" method="post" novalidate use:form.action>
            <Dialog.Header>
                <Dialog.Title>Create User</Dialog.Title>
            </Dialog.Header>
            <div class="flex flex-col gap-2">
                <div class="flex-row gap-1.5">
                    <Label for="{id}-name" required>Name</Label>
                    <Input
                        id="{id}-name"
                        name="name"
                        bind:value={form.data.name}
                        disabled={loading.value}
                    />
                    <FieldError error={form.errors.name} />
                </div>
                <div class="flex-row gap-1.5">
                    <Label for="{id}-email" required>Email</Label>
                    <Input
                        id="{id}-email"
                        name="email"
                        bind:value={form.data.email}
                        disabled={loading.value}
                    />
                    <FieldError error={form.errors.email} />
                </div>
                <div class="flex-row gap-1.5">
                    <Label for="{id}-password" required>Password</Label>
                    <Input
                        id="{id}-password"
                        name="password"
                        type="password"
                        bind:value={form.data.password}
                        disabled={loading.value}
                    />
                    <FieldError error={form.errors.password} />
                </div>
                <div class="flex-row gap-1.5">
                    <Label for="{id}-password_confirmation" required>Password Confirmation</Label>
                    <Input
                        id="{id}-password_confirmation"
                        name="password_confirmation"
                        type="password"
                        bind:value={form.data.password_confirmation}
                        disabled={loading.value}
                    />
                    <FieldError error={form.errors.password_confirmation} />
                </div>
            </div>
            <Dialog.Footer>
                <Dialog.Close
                    type="button"
                    class={buttonVariants({ variant: 'secondary' })}
                    disabled={loading.value}
                >
                    Cancel
                </Dialog.Close>
                <Button type="submit" loading={processing.value} disabled={loading.value}>
                    Create
                </Button>
            </Dialog.Footer>
        </form>
    </Dialog.Content>
</Dialog.Root>
