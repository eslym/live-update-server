<script lang="ts">
    import * as Dialog from '$lib/components/ui/dialog';
    import type { Snippet } from 'svelte';
    import { useForm } from '@/inertia';
    import { loading } from '$lib/loading.svelte';
    import { Label } from '$lib/components/ui/label';
    import { Input } from '$lib/components/ui/input';
    import { Button, buttonVariants } from '$lib/components/ui/button';
    import FieldError from '$lib/components/FieldError.svelte';
    import * as DatePicker from '$lib/components/ui/date-picker';
    import type { CalendarDateTime } from '@internationalized/date';

    type _keep = [typeof DatePicker];

    let {
        open = $bindable(false),
        children = undefined
    }: { open?: boolean; children?: Snippet<[typeof Dialog.Trigger]> } = $props();

    const id = $props.id();

    const form = useForm({
        name: '',
        expires_at: undefined as CalendarDateTime | undefined
    }).transform((data) => ({
        name: data.name,
        expires_at: data.expires_at ? data.expires_at.toString() : null
    }));

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
        if (val) form.reset();
    }}
>
    {@render children?.(Dialog.Trigger)}
    <Dialog.Content>
        <form class="contents" action="/tokens" method="post" novalidate use:form.action>
            <Dialog.Header>
                <Dialog.Title>Create API Token</Dialog.Title>
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
                    <DatePicker.Root
                        bind:value={form.data.expires_at}
                        weekdayFormat="short"
                        granularity="minute"
                        disabled={loading.value}
                        hourCycle={24}
                    >
                        <DatePicker.Label>Expires At</DatePicker.Label>
                        <DatePicker.Input />
                        <DatePicker.Content>
                            <DatePicker.Calendar />
                        </DatePicker.Content>
                    </DatePicker.Root>
                    <FieldError error={form.errors.expires_at} />
                    <span class="text-xs text-muted-foreground">
                        Leave empty for no expiration
                    </span>
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
