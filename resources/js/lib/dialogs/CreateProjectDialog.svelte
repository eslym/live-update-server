<script lang="ts">
    import * as Dialog from '$lib/components/ui/dialog';
    import type { Snippet } from 'svelte';
    import { useForm } from '@/inertia';
    import { loading } from '$lib/loading.svelte';
    import { Button, buttonVariants } from '$lib/components/ui/button';
    import { Label } from '$lib/components/ui/label';
    import { Input } from '$lib/components/ui/input';
    import FieldError from '$lib/components/FieldError.svelte';
    import { Textarea } from '$lib/components/ui/textarea';

    let {
        open = $bindable(false),
        children = undefined
    }: {
        open?: boolean;
        children?: Snippet<[Trigger: typeof Dialog.Trigger]>;
    } = $props();

    const id = $props.id();

    const form = useForm.derived(() => ({
        name: '',
        description: '',
        private_key: ''
    }));

    const processing = loading.derived(() => form.processing);
</script>

<Dialog.Root
    bind:open
    onOpenChange={(val) => {
        if (val) {
            form.reset();
            form.errors = {};
        }
    }}
>
    {@render children?.(Dialog.Trigger)}
    <Dialog.Content>
        <form class="contents" action="/projects" method="post" novalidate use:form.action>
            <Dialog.Header>
                <Dialog.Title>Create Project</Dialog.Title>
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
                <div class="flex flex-col gap-1.5">
                    <Label for="{id}-description">Description</Label>
                    <Textarea
                        id="{id}-description"
                        name="description"
                        bind:value={form.data.description}
                        disabled={loading.value}
                        rows={5}
                    />
                    <FieldError error={form.errors.description} />
                </div>
                <div class="flex flex-col gap-1.5">
                    <Label for="{id}-private-key">Private Key</Label>
                    <Textarea
                        id="{id}-private-key"
                        name="private_key"
                        bind:value={form.data.private_key}
                        disabled={loading.value}
                        placeholder="Leave empty to generate a new key"
                        class="font-mono"
                        rows={5}
                    />
                    <FieldError error={form.errors.private_key} />
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
