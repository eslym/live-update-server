<script lang="ts">
    import * as Dialog from '$lib/components/ui/dialog';
    import { useForm } from '@/inertia';
    import FieldError from '@/lib/components/FieldError.svelte';
    import { Input } from '@/lib/components/ui/input';
    import { Label } from '@/lib/components/ui/label';
    import { loading } from '@/lib/loading.svelte';
    import type { Snippet } from 'svelte';
    import * as Select from '$lib/components/ui/select';
    import { Switch } from '@/lib/components/ui/switch';
    import { Badge } from '@/lib/components/ui/badge';
    import { Button, buttonVariants } from '@/lib/components/ui/button';

    let {
        open = $bindable(false),
        project_id,
        version,
        children,
        channels
    }: {
        open?: boolean;
        project_id: string;
        version: {
            nanoid: string;
            name: string;
            channels: (string | null)[];
            reqs: VersionRequirements;
        };
        channels: string[];
        children?: Snippet<[Trigger: typeof Dialog.Trigger]>;
    } = $props();

    const id = $props.id();

    const form = useForm.derived(() => ({
        name: version.name,
        channels: version.channels.map((c) => c ?? ''),
        reqs: version.reqs
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
            action="/projects/{project_id}/versions/{version.nanoid}"
            method="POST"
            novalidate
            use:form.action
        >
            <Dialog.Header>
                <Dialog.Title>Edit Version</Dialog.Title>
            </Dialog.Header>
            <div class="flex flex-col gap-2 py-2.5">
                <div class="flex flex-col gap-1.5">
                    <Label for="{id}-name">Name</Label>
                    <Input
                        type="text"
                        id="{id}-name"
                        name="name"
                        bind:value={form.data.name}
                        disabled={loading.value}
                    />
                    <FieldError error={form.errors.name} />
                </div>
                <div class="flex flex-col gap-1.5">
                    <Label for="{id}-channels">Channels</Label>
                    <Select.Root
                        name="channels"
                        type="multiple"
                        bind:value={form.data.channels}
                        disabled={loading.value}
                    >
                        <Select.Trigger id="{id}-channels">
                            <div class="flex flex-row flex-wrap gap-1">
                                {#each form.data.channels as channel (channel)}
                                    <Badge
                                        variant={channel ? 'secondary' : 'outline'}
                                        class={channel ? '' : 'text-muted-foreground'}
                                    >
                                        {channel || '(default)'}
                                    </Badge>
                                {/each}
                            </div>
                        </Select.Trigger>
                        <Select.Content>
                            <Select.Item value="" class="text-muted-foreground"
                                >(default)</Select.Item
                            >
                            {#each channels as channel (channel)}
                                <Select.Item value={channel}>
                                    {channel}
                                </Select.Item>
                            {/each}
                        </Select.Content>
                    </Select.Root>
                    <FieldError error={form.errors.channels} />
                </div>
                <div class="flex flex-col gap-1.5 mt-8">
                    <div class="flex flex-row justify-between items-center">
                        <Label for="{id}-android-available">Android Version (versionCode)</Label>
                        <Switch
                            id="{id}-android-available"
                            name="android_available"
                            bind:checked={
                                () => Boolean(form.data.reqs.android),
                                (val) => (form.data.reqs.android = val ? {} : undefined)
                            }
                            disabled={loading.or(!form.data.reqs.ios)}
                        />
                    </div>
                    <div class="flex flex-row items-center gap-1.5">
                        <Input
                            type="number"
                            id="{id}-android-min"
                            name="reqs[android][min]"
                            bind:value={
                                () => form.data.reqs.android?.min,
                                (val) => {
                                    if (form.data.reqs.android) {
                                        form.data.reqs.android.min = val;
                                    }
                                }
                            }
                            disabled={loading.or(!form.data.reqs.android)}
                            placeholder="Min (optional)"
                            class="flex-grow"
                        />
                        -
                        <Input
                            type="number"
                            id="{id}-android-max"
                            name="reqs[android][max]"
                            bind:value={
                                () => form.data.reqs.android?.max,
                                (val) => {
                                    if (form.data.reqs.android) {
                                        form.data.reqs.android.max = val;
                                    }
                                }
                            }
                            disabled={loading.or(!form.data.reqs.android)}
                            placeholder="Max (optional)"
                            class="flex-grow"
                        />
                    </div>
                    <FieldError error={form.allErrors('reqs.android')} />
                </div>
                <div class="flex flex-col gap-1.5">
                    <div class="flex flex-row justify-between items-center">
                        <Label for="{id}-ios-available">iOS Version (CFBundleVersion)</Label>
                        <Switch
                            id="{id}-ios-available"
                            name="ios_available"
                            bind:checked={
                                () => Boolean(form.data.reqs.ios),
                                (val) => (form.data.reqs.ios = val ? {} : undefined)
                            }
                            disabled={loading.or(!form.data.reqs.android)}
                        />
                    </div>
                    <div class="flex flex-row items-center gap-1.5">
                        <Input
                            type="number"
                            id="{id}-ios-min"
                            name="reqs[ios][min]"
                            bind:value={
                                () => form.data.reqs.ios?.min,
                                (val) => {
                                    if (form.data.reqs.ios) {
                                        form.data.reqs.ios.min = val;
                                    }
                                }
                            }
                            disabled={loading.or(!form.data.reqs.ios)}
                            placeholder="Min (optional)"
                            class="flex-grow"
                        />
                        -
                        <Input
                            type="number"
                            id="{id}-ios-max"
                            name="reqs[ios][max]"
                            bind:value={
                                () => form.data.reqs.ios?.max,
                                (val) => {
                                    if (form.data.reqs.ios) {
                                        form.data.reqs.ios.max = val;
                                    }
                                }
                            }
                            disabled={loading.or(!form.data.reqs.ios)}
                            placeholder="Max (optional)"
                            class="flex-grow"
                        />
                    </div>
                    <FieldError error={form.allErrors('reqs.ios')} />
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
                    Save
                </Button>
            </Dialog.Footer>
        </form>
    </Dialog.Content>
</Dialog.Root>
