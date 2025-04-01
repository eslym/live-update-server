<script lang="ts">
    import * as Sidebar from '$lib/components/ui/sidebar';
    import * as Breadcrumb from '$lib/components/ui/breadcrumb';
    import { Separator } from '$lib/components/ui/separator';
    import type { Snippet } from 'svelte';
    import { Input } from '$lib/components/ui/input';
    import { SearchIcon } from '@lucide/svelte';
    import { config } from '$lib/config';

    type _keep = [typeof Sidebar, typeof Breadcrumb];

    type BreadcrumbItem = {
        label: string;
        href?: string;
        dropdown?: {
            label: string;
            href?: string;
        }[];
    };

    let {
        children,
        breadcrumbs = [],
        searchable = false,
        search = $bindable(),
        title = undefined
    }: {
        children: Snippet;
        breadcrumbs?: BreadcrumbItem[];
        searchable?: boolean;
        search?: string;
        title?: string;
    } = $props();
</script>

<svelte:head>
    {#if title}
        <title>{title} | {config.APP_NAME}</title>
    {:else}
        <title>{config.APP_NAME}</title>
    {/if}
</svelte:head>

<header class={['grid grid-cols-[auto,auto,1fr]', searchable || 'pr-4']}>
    <Breadcrumb.Root>
        <Breadcrumb.List class="pl-4 pr-16 py-2">
            <Breadcrumb.Item>
                <Sidebar.Trigger />
            </Breadcrumb.Item>
            {#each breadcrumbs ?? [] as item}
                <Breadcrumb.Separator />
                <Breadcrumb.Item>
                    {#if item.href}
                        <Breadcrumb.Link href={item.href}>
                            {item.label}
                        </Breadcrumb.Link>
                    {:else}
                        {item.label}
                    {/if}
                </Breadcrumb.Item>
            {/each}
        </Breadcrumb.List>
    </Breadcrumb.Root>
    {#if searchable}
        <Separator orientation="vertical" />
        <div
            class="flex flex-row gap-1.5 items-center pl-4 w-full focus-within:ring-1 focus-within:ring-ring z-10 rounded-tr-md"
        >
            <SearchIcon class="size-4 text-muted-foreground" />
            <Input
                bind:value={search}
                placeholder="Search"
                class="w-0 flex-grow h-full border-none rounded-l-none rounded-br-none ring-0 focus-visible:ring-0"
            />
        </div>
    {/if}
</header>
<Separator />
<div class="flex-grow overflow-auto @container">
    {@render children()}
</div>
