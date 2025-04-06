<script lang="ts">
    import * as Dialog from '$lib/components/ui/dialog';
    import type { Snippet } from 'svelte';
    import { useForm } from '@/inertia';
    import { loading } from '$lib/loading.svelte';
    import { type RequiredContentable, content } from '$lib/utils.svelte';
    import { Button, buttonVariants } from '$lib/components/ui/button';

    let {
        open = $bindable(false),
        children = undefined,
        action,
        title,
        description
    }: {
        open?: boolean;
        children?: Snippet<[Trigger: typeof Dialog.Trigger]>;
        action: string;
        title: RequiredContentable;
        description: RequiredContentable;
    } = $props();

    const form = useForm({});
    const processing = loading.derived(() => form.processing);
</script>

<Dialog.Root
    bind:open={
        () => open,
        (val) => {
            if (!processing.value) open = val;
        }
    }
>
    {@render children?.(Dialog.Trigger)}
    <Dialog.Content>
        <form class="contents" {action} novalidate use:form.action={{ method: 'delete' }}>
            <Dialog.Header>
                <Dialog.Title>{@render content(title)}</Dialog.Title>
                <Dialog.Description>
                    {@render content(description)}
                </Dialog.Description>
            </Dialog.Header>
            <Dialog.Footer>
                <Dialog.Close
                    type="button"
                    class={buttonVariants({ variant: 'secondary' })}
                    disabled={processing.value}
                >
                    Cancel
                </Dialog.Close>
                <Button
                    type="submit"
                    variant="destructive"
                    loading={processing.value}
                    disabled={loading.value}
                >
                    Delete
                </Button>
            </Dialog.Footer>
        </form>
    </Dialog.Content>
</Dialog.Root>
