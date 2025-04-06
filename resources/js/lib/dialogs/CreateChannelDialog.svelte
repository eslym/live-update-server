<script lang="ts">
    import * as Dialog from '$lib/components/ui/dialog';
    import { useForm } from '@/inertia';
    import { loading } from '$lib/loading.svelte';
    import type { Snippet } from 'svelte';
    import { Label } from '@/lib/components/ui/label';
    import { Input } from '@/lib/components/ui/input';
    import FieldError from '@/lib/components/FieldError.svelte';
    import { Button, buttonVariants } from '@/lib/components/ui/button';

    let {
        open = $bindable(false),
        children = undefined,
        project_id
    }: {
        open?: boolean;
        children?: Snippet<[Trigger: typeof Dialog.Trigger]>;
        project_id: string;
    } = $props();

    const id = $props.id();
    const form = useForm({
        name: ''
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
        <form
            class="contents"
            action="/projects/{project_id}/channels"
            method="post"
            novalidate
            use:form.action
        >
            <Dialog.Header>
                <Dialog.Title>Create Channel</Dialog.Title>
            </Dialog.Header>
            <div class="flex flex-col gap-2">
                <div class="flex flex-col gap-1.5">
                    <Label for="{id}-name" required>Name</Label>
                    <Input
                        id="{id}-name"
                        name="name"
                        bind:value={form.data.name}
                        disabled={loading.value}
                    />
                    <FieldError error={form.errors.name} />
                </div>
            </div>
            <Dialog.Footer>
                <Dialog.Close type="button" class={buttonVariants({ variant: 'secondary' })}>
                    Cancel
                </Dialog.Close>
                <Button type="submit">Create</Button>
            </Dialog.Footer>
        </form>
    </Dialog.Content>
</Dialog.Root>
