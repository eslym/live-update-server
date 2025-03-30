<script lang="ts">
    import type { Snippet } from 'svelte';
    import * as Sidebar from '$lib/components/ui/sidebar';
    import * as Dropdown from '$lib/components/ui/dropdown-menu';
    import * as Breadcrumb from '$lib/components/ui/breadcrumb';
    import { config } from '$lib/config';
    import { inertia } from '@/inertia';
    import {
        ChevronRightIcon,
        LaptopMinimalIcon,
        UserRoundIcon,
        SunIcon,
        MoonIcon,
        LogOutIcon,
        FolderKanbanIcon,
        UsersRoundIcon,
        LockKeyholeIcon
    } from '@lucide/svelte';
    import { local } from '$lib/storage';
    import { Separator } from '$lib/components/ui/separator';

    let {
        route,
        children,
        user,
        breadcrumbs = [],
        title
    }: {
        route: string;
        children: Snippet;
        breadcrumbs?: { label: string; href?: string }[] | null;
        title?: string;
        user: {
            id: number;
            nanoid: string;
            name: string;
            email: string;
            ['2fa_enabled']: boolean;
        };
    } = $props();
</script>

<svelte:head>
    {#if title}
        <title>{title} | {config.APP_NAME}</title>
    {:else}
        <title>{config.APP_NAME}</title>
    {/if}
</svelte:head>

<Sidebar.Provider>
    <Sidebar.Root variant="inset" collapsible="icon">
        <Sidebar.Header>
            <Sidebar.MenuButton class="flex flex-row items-center gap-2.5 h-auto">
                {#snippet child({ props })}
                    <a {...props} href="/" use:inertia>
                        <img src="/favicon.svg" alt="logo" class="size-10" />
                        <div class="flex flex-col gap-0.5 overflow-hidden text-nowrap">
                            <span class="font-bold">{config.APP_NAME}</span>
                            <span class="text-muted-foreground text-sm"> Distribute your App </span>
                        </div>
                    </a>
                {/snippet}
            </Sidebar.MenuButton>
            <Sidebar.Separator />
        </Sidebar.Header>
        <Sidebar.Content>
            <Sidebar.Group>
                <Sidebar.GroupContent>
                    <Sidebar.Menu>
                        <Sidebar.MenuItem>
                            <Sidebar.MenuButton isActive={route?.startsWith('project.')}>
                                {#snippet child({ props })}
                                    <a {...props} href="/projects" use:inertia>
                                        <FolderKanbanIcon class="size-4 mr-2" />
                                        Projects
                                    </a>
                                {/snippet}
                            </Sidebar.MenuButton>
                        </Sidebar.MenuItem>
                        <Sidebar.MenuItem>
                            <Sidebar.MenuButton isActive={route?.startsWith('account.')}>
                                {#snippet child({ props })}
                                    <a {...props} href="/accounts" use:inertia>
                                        <UsersRoundIcon class="size-4 mr-2" />
                                        Accounts
                                    </a>
                                {/snippet}
                            </Sidebar.MenuButton>
                        </Sidebar.MenuItem>
                        <Sidebar.MenuItem>
                            <Sidebar.MenuButton isActive={route?.startsWith('token.')}>
                                {#snippet child({ props })}
                                    <a {...props} href="/tokens" use:inertia>
                                        <LockKeyholeIcon class="size-4 mr-2" />
                                        Tokens
                                    </a>
                                {/snippet}
                            </Sidebar.MenuButton>
                        </Sidebar.MenuItem>
                    </Sidebar.Menu>
                </Sidebar.GroupContent>
            </Sidebar.Group>
        </Sidebar.Content>
        <Sidebar.Footer>
            <Dropdown.Root>
                <Dropdown.Trigger>
                    {#snippet child({ props })}
                        <Sidebar.MenuButton
                            {...props}
                            class="flex flex-row-reverse h-auto"
                            isActive={route?.startsWith('profile.')}
                        >
                            <ChevronRightIcon />
                            <div
                                class="flex-grow flex flex-col gap-0.5 h-auto items-start overflow-hidden text-nowrap"
                            >
                                <span class="font-semibold">{user.name}</span>
                                <span class="text-muted-foreground text-sm">{user.email}</span>
                            </div>
                        </Sidebar.MenuButton>
                    {/snippet}
                </Dropdown.Trigger>
                <Dropdown.Content side="right" align="start">
                    <Dropdown.Group>
                        <Dropdown.GroupHeading>Theme</Dropdown.GroupHeading>
                        <Dropdown.RadioGroup
                            bind:value={
                                () => local.theme ?? 'system',
                                (val) => {
                                    local.theme = val === 'system' ? null : val;
                                }
                            }
                        >
                            <Dropdown.RadioItem value="system">
                                <LaptopMinimalIcon class="size-4 mr-2" />
                                System Defined
                            </Dropdown.RadioItem>
                            <Dropdown.RadioItem value="light">
                                <SunIcon class="size-4 mr-2" />
                                Light Theme
                            </Dropdown.RadioItem>
                            <Dropdown.RadioItem value="dark">
                                <MoonIcon class="size-4 mr-2" />
                                Dark Theme
                            </Dropdown.RadioItem>
                        </Dropdown.RadioGroup>
                    </Dropdown.Group>
                    <Dropdown.Group>
                        <Dropdown.Separator />
                        <Dropdown.Item>
                            {#snippet child({ props })}
                                <a {...props} href="/profile" use:inertia>
                                    <UserRoundIcon class="mr-2" />
                                    Profile
                                </a>
                            {/snippet}
                        </Dropdown.Item>
                        <Dropdown.Item>
                            {#snippet child({ props })}
                                <a {...props} href="/logout" use:inertia>
                                    <LogOutIcon class="mr-2" />
                                    Logout
                                </a>
                            {/snippet}
                        </Dropdown.Item>
                    </Dropdown.Group>
                </Dropdown.Content>
            </Dropdown.Root>
        </Sidebar.Footer>
    </Sidebar.Root>
    <Sidebar.Inset>
        <header class="px-4 py-2 flex flex-row justify-between gap-8 font-semibold">
            <Breadcrumb.Root>
                <Breadcrumb.List>
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
            {#if title}
                <h1>{title}</h1>
            {/if}
        </header>
        <Separator />
        <div class="flex-grow overflow-auto @container">
            {@render children()}
        </div>
    </Sidebar.Inset>
</Sidebar.Provider>
