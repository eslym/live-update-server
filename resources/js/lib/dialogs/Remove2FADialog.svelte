<script lang="ts">
    import * as Dialog from '$lib/components/ui/dialog';

    import type { Snippet } from 'svelte';
    import { useForm } from '@/inertia';
    import { loading } from '$lib/loading.svelte';
    import { Label } from '$lib/components/ui/label';
    import { Input } from '$lib/components/ui/input';
    import FieldError from '$lib/components/FieldError.svelte';
    import { Button, buttonVariants } from '$lib/components/ui/button';

    let {
        open = $bindable(false),
        children = undefined
    }: {
        open?: boolean;
        children?: Snippet<[Trigger: typeof Dialog.Trigger]>;
    } = $props();

    const id = $props.id();

    const form = useForm({
        password: ''
    });

    const processing = loading.derived(() => form.processing);
</script>

<Dialog.Root
    bind:open={
        () => open,
        (val) => {
            if (processing.value) return;
            open = val;
        }
    }
    onOpenChange={(val) => {
        if (!val) return;
        form.reset();
    }}
>
    {@render children?.(Dialog.Trigger)}
    <Dialog.Content>
        <form class="contents" method="post" action="/2fa/disable" use:form.action>
            <Dialog.Header>
                <Dialog.Title>Remove 2FA</Dialog.Title>
                <Dialog.Description>
                    Enter your password to remove 2FA from your account.
                </Dialog.Description>
            </Dialog.Header>
            <div class="flex flex-col gap-1.5">
                <Label for="{id}-password" required>Password</Label>
                <Input
                    id="{id}-password"
                    type="password"
                    name="password"
                    bind:value={form.data.password}
                    disabled={loading.value}
                    required
                />
                <FieldError error={form.errors.password} />
            </div>
            <Dialog.Footer>
                <Dialog.Close class={buttonVariants({ variant: 'secondary' })}>Cancel</Dialog.Close>
                <Button type="submit" disabled={loading.value} loading={processing.value}>
                    Remove 2FA
                </Button>
            </Dialog.Footer>
        </form>
    </Dialog.Content>
</Dialog.Root>
