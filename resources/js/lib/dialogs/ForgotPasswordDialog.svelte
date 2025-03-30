<script lang="ts">
    import * as Dialog from '$lib/components/ui/dialog';
    import { loading } from '$lib/loading.svelte';
    import { useForm } from '@/inertia';
    import type { Snippet } from 'svelte';
    import { Label } from '$lib/components/ui/label';
    import { Input } from '$lib/components/ui/input';
    import { Button, buttonVariants } from '$lib/components/ui/button';
    import FieldError from '$lib/components/FieldError.svelte';

    let {
        open = $bindable(false),
        email = '',
        children = undefined
    }: {
        open?: boolean;
        email?: string;
        children?: Snippet<[Trigger: typeof Dialog.Trigger]>;
    } = $props();

    const id = $props.id();

    const form = useForm({
        email
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
        form.default.email = email;
        form.reset();
    }}
>
    {@render children?.(Dialog.Trigger)}
    <Dialog.Content>
        <form
            class="contents"
            method="post"
            action="/forgot-password"
            novalidate
            onsubmit={form.handleSubmit}
        >
            <Dialog.Header>
                <Dialog.Title>Forgot Password</Dialog.Title>
                <Dialog.Description>
                    Enter your email address and we will send you a link to reset your password.
                </Dialog.Description>
            </Dialog.Header>
            <div class="flex flex-col gap-1.5">
                <Label for={id} required>Email</Label>
                <Input
                    {id}
                    type="email"
                    name="email"
                    bind:value={form.data.email}
                    disabled={loading.value}
                />
                <FieldError error={form.errors.email} />
            </div>
            <Dialog.Footer>
                <Dialog.Close
                    type="button"
                    class={buttonVariants({ variant: 'secondary' })}
                    disabled={loading.value}>Cancel</Dialog.Close
                >
                <Button type="submit" loading={processing.value} disabled={loading.value}>
                    Send Reset Link
                </Button>
            </Dialog.Footer>
        </form>
    </Dialog.Content>
</Dialog.Root>
