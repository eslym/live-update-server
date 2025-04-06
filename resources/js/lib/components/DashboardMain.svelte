<script lang="ts">
    import * as Sidebar from '$lib/components/ui/sidebar';
    import * as Breadcrumb from '$lib/components/ui/breadcrumb';
    import * as Dropdown from '$lib/components/ui/dropdown-menu';
    import { Separator } from '$lib/components/ui/separator';
    import { ScrollArea } from '$lib/components/ui/scroll-area';
    import type { Snippet } from 'svelte';
    import { Input } from '$lib/components/ui/input';
    import {
        ChevronRightIcon,
        ChevronsLeftIcon,
        ChevronsRightIcon,
        SearchIcon
    } from '@lucide/svelte';
    import { config } from '$lib/config';
    import { Button } from '$lib/components/ui/button';
    import { loading } from '$lib/loading.svelte';
    import { cn } from '$lib/utils';
    import { inertia } from '@/inertia';

    type BreadcrumbItem = {
        label: string;
    } & (
        | {
              href?: string;
              dropdown?: undefined;
          }
        | {
              href?: undefined;
              dropdown: {
                  label: string;
                  href?: string;
                  active?: boolean;
              }[];
          }
    );

    let {
        children = undefined,
        actions = undefined,
        breadcrumbs = [],
        searchable = false,
        search = $bindable(),
        title = undefined,
        pagination = undefined,
        onpageclick = undefined
    }: {
        children?: Snippet;
        actions?: Snippet;
        breadcrumbs?: BreadcrumbItem[];
        searchable?: boolean;
        search?: string;
        title?: string;
        pagination?: Pagination<any>;
        onpageclick?: (page: number) => void;
    } = $props();

    function pages(meta: Pagination<any>['meta']) {
        let start = Math.max(1, meta.current_page - 1);
        const end = Math.min(meta.last_page, start + 3);
        if (start > 1 && end - start < 2) {
            start = Math.max(1, end - 3);
        }
        return new Array(end - start + 1).fill(0).map((_, i) => start + i);
    }
</script>

<svelte:head>
    {#if title}
        <title>{title} | {config.APP_NAME}</title>
    {:else}
        <title>{config.APP_NAME}</title>
    {/if}
</svelte:head>

<div class="flex-grow grid grid-rows-[auto,auto,1fr] max-h-full">
    <header class={['flex flex-row items-stretch last:*:!rounded-tr-xl']}>
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
                        {:else if item.dropdown}
                            <Dropdown.Root>
                                <Dropdown.Trigger>
                                    {item.label}
                                </Dropdown.Trigger>
                                <Dropdown.Content align="start">
                                    {#each item.dropdown as dropdown}
                                        {#if dropdown.href}
                                            {@const href = dropdown.href}
                                            <Dropdown.Item>
                                                {#snippet child({ props })}
                                                    <a {...props} {href} use:inertia>
                                                        <ChevronRightIcon
                                                            class={cn(
                                                                'size-4',
                                                                dropdown.active || 'opacity-0'
                                                            )}
                                                        />
                                                        {dropdown.label}
                                                    </a>
                                                {/snippet}
                                            </Dropdown.Item>
                                        {:else}
                                            <Dropdown.Item>
                                                <ChevronRightIcon
                                                    class={cn(
                                                        'size-4',
                                                        dropdown.active || 'opacity-0'
                                                    )}
                                                />
                                                {dropdown.label}
                                            </Dropdown.Item>
                                        {/if}
                                    {/each}
                                </Dropdown.Content>
                            </Dropdown.Root>
                        {:else}
                            {item.label}
                        {/if}
                    </Breadcrumb.Item>
                {/each}
            </Breadcrumb.List>
        </Breadcrumb.Root>
        {#if searchable}
            <div
                class="border-l border-l-border flex flex-row gap-0.5 items-center pl-4 w-0 flex-grow focus-within:ring-1 focus-within:ring-ring z-10"
            >
                <SearchIcon class="size-4 text-muted-foreground" />
                <Input
                    bind:value={search}
                    placeholder="Search"
                    class="w-0 flex-grow h-full border-none rounded-l-none rounded-br-none ring-0 focus-visible:ring-0"
                />
            </div>
        {:else}
            <div class="flex-grow"></div>
        {/if}
        {@render actions?.()}
        {#if pagination}
            {@const numbers = pages(pagination.meta)}
            <Separator orientation="vertical" />
            <Button
                variant="ghost"
                class="rounded-none h-full"
                disabled={loading.or(pagination.meta.current_page <= 1)}
                onclick={() => onpageclick?.(1)}
            >
                <ChevronsLeftIcon class="size-4" />
            </Button>
            {#each numbers as page}
                {@const active = pagination.meta.current_page === page}
                <Separator orientation="vertical" />
                <Button
                    variant="ghost"
                    class={cn(
                        'rounded-none h-full',
                        active && 'font-bold disabled:opacity-100 disabled:text-foreground'
                    )}
                    disabled={loading.or(active)}
                    onclick={() => onpageclick?.(page)}
                >
                    {page}
                </Button>
            {/each}
            <Separator orientation="vertical" />
            <Button
                variant="ghost"
                class="rounded-none h-full"
                disabled={loading.or(pagination.meta.current_page >= pagination.meta.last_page)}
                onclick={() => onpageclick?.(pagination.meta.last_page)}
            >
                <ChevronsRightIcon class="size-4" />
            </Button>
            <div
                class="border-l border-l-border flex items-center px-4 text-sm text-muted-foreground"
            >
                {pagination.meta.from ?? 0} - {pagination.meta.to ?? 0} of
                {pagination.meta.total}
            </div>
        {/if}
    </header>
    <Separator />
    <ScrollArea class="@container rounded-b-xl">
        {@render children?.()}
    </ScrollArea>
</div>
