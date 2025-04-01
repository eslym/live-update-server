<script lang="ts">
    import * as Dialog from '$lib/components/ui/dialog';
    import { onMount, type Snippet } from 'svelte';
    import { buttonVariants } from '../components/ui/button';

    let {
        open = $bindable(false),
        title,
        description,
        children = undefined,
        openOnMount = false
    }: {
        open?: boolean;
        title: string;
        description: string;
        children?: Snippet<[Trigger: typeof Dialog.Trigger]>;
        openOnMount?: boolean;
    } = $props();

    onMount(() => {
        if (openOnMount) {
            open = true;
        }
    });
</script>

<Dialog.Root bind:open>
    {@render children?.(Dialog.Trigger)}
    <Dialog.Content>
        <Dialog.Header>
            <Dialog.Title>{title}</Dialog.Title>
            <Dialog.Description>{description}</Dialog.Description>
        </Dialog.Header>
        <Dialog.Footer>
            <Dialog.Close class="{buttonVariants({ variant: 'secondary' })}}">Close</Dialog.Close>
        </Dialog.Footer>
    </Dialog.Content>
</Dialog.Root>
