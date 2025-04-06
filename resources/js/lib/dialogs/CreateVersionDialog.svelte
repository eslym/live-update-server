<script lang="ts">
    import * as Dialog from '$lib/components/ui/dialog';
    import { onDestroy, type Snippet } from 'svelte';
    import { useForm } from '@/inertia';
    import { Upload } from 'tus-js-client';
    import { Tween } from 'svelte/motion';
    import { Button, buttonVariants } from '$lib/components/ui/button';
    import { loading } from '$lib/loading.svelte';
    import { Label } from '$lib/components/ui/label';
    import { Input } from '$lib/components/ui/input';
    import * as Select from '$lib/components/ui/select';
    import FieldError from '$lib/components/FieldError.svelte';
    import Badge from '@/lib/components/ui/badge/badge.svelte';
    import Progress from '@/lib/components/ui/progress/progress.svelte';
    import { XIcon } from '@lucide/svelte';
    import Switch from '@/lib/components/ui/switch/switch.svelte';

    let {
        project_id,
        open = $bindable(false),
        children = undefined,
        channels = []
    }: {
        project_id: string;
        open?: boolean;
        children?: Snippet<[Trigger: typeof Dialog.Trigger]>;
        channels?: string[];
    } = $props();

    let upload: Upload | null = $state.raw(null);
    const upload_progress = new Tween(0, { duration: 500 });

    let reset_key = $state(performance.now());

    const id = $props.id();

    const form = useForm({
        name: '',
        bundle_file: null as string | null,
        channels: [''],
        reqs: {
            android: {},
            ios: {}
        } as VersionRequirements
    });

    const processing = loading.derived(() => form.processing);

    onDestroy(() => {
        upload?.abort(true);
    });
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
            upload = null;
            form.reset();
            form.errors = {};
            upload_progress.set(0, { duration: 0 });
        } else {
            upload?.abort(true);
        }
    }}
>
    {@render children?.(Dialog.Trigger)}
    <Dialog.Content>
        <form
            class="contents"
            action="/projects/{project_id}/versions"
            method="post"
            novalidate
            use:form.action
        >
            <Dialog.Header>
                <Dialog.Title>Create New Version</Dialog.Title>
                <Dialog.Description>
                    Upload <code>.zip</code> file containing <code>index.html</code>. Specify app
                    version (<code>CFBundleVersion</code> for iOS, <code>versionCode</code> for Android)
                    which supported by the bundle.
                </Dialog.Description>
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
                <div class="flex flex-col gap-1.5">
                    <Label for="{id}-bundle">Bundle</Label>
                    {#if upload}
                        {#if upload_progress.current < 100 || !form.data.bundle_file}
                            <Progress value={upload_progress.current} class="w-full" />
                        {:else}
                            <div class="grid grid-cols-[1fr,auto] gap-1.5">
                                <Input value="{form.data.bundle_file}.zip" readonly />
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    disabled={loading.value}
                                    onclick={() => {
                                        upload?.abort(true);
                                        upload = null;
                                        form.data.bundle_file = null;
                                        upload_progress.set(0, { duration: 0 });
                                    }}
                                >
                                    <XIcon class="size-4" />
                                </Button>
                            </div>
                        {/if}
                    {:else}
                        {#key reset_key}
                            <Input
                                type="file"
                                id="{id}-bundle"
                                name="bundle_file"
                                accept=".zip"
                                disabled={loading.value}
                                class={upload || form.data.bundle_file ? 'hidden' : ''}
                                onchange={(ev) => {
                                    const file = ev.currentTarget.files?.[0];
                                    if (!file) return;
                                    reset_key = performance.now();
                                    upload = new Upload(file, {
                                        endpoint: '/uploads',
                                        metadata: {
                                            name: file.name
                                        },
                                        chunkSize: 5 * 1024 * 1024,
                                        onError: (err) => {
                                            console.error(err);
                                            upload?.abort(true);
                                            upload = null;
                                            form.errors.bundle_file = 'Upload failed';
                                        },
                                        onProgress: (bytesUploaded, bytesTotal) => {
                                            upload_progress.target =
                                                (bytesUploaded / bytesTotal) * 100;
                                        },
                                        onSuccess: async () => {
                                            const url = new URL(upload!.url!);
                                            form.data.bundle_file = url.pathname.replace(
                                                '/uploads/',
                                                ''
                                            );
                                            upload_progress.target = 100;
                                        }
                                    });
                                    upload.start();
                                    upload_progress.set(0, { duration: 0 });
                                }}
                            />
                        {/key}
                    {/if}
                    <FieldError error={form.errors.bundle_file} />
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
                    Create
                </Button>
            </Dialog.Footer>
        </form>
    </Dialog.Content>
</Dialog.Root>
