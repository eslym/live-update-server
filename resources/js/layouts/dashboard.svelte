<script lang="ts" module>
    function isRoute(route: string | null, group: string) {
        return Boolean(route && (route === group || route.startsWith(group + '.')));
    }
</script>

<script lang="ts">
    import type { Snippet } from 'svelte';
    import * as Sidebar from '$lib/components/ui/sidebar';
    import * as Dropdown from '$lib/components/ui/dropdown-menu';
    import * as Dialog from '$lib/components/ui/dialog';
    import { config } from '$lib/config';
    import { inertia, router } from '@/inertia';
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
    import { loading } from '$lib/loading.svelte';
    import { Button, buttonVariants } from '$lib/components/ui/button';

    let {
        route,
        children,
        user
    }: {
        route: string | null;
        children: Snippet;
        user: User;
    } = $props();

    let logout_dialog = $state(false);
    const logging_out = loading.local();
</script>

<Sidebar.Provider>
    <Sidebar.Root variant="inset" collapsible="icon" inert={loading.value}>
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
                            <Sidebar.MenuButton isActive={isRoute(route, 'project')}>
                                {#snippet child({ props })}
                                    <a {...props} href="/projects" use:inertia>
                                        <FolderKanbanIcon class="size-4 mr-2" />
                                        Projects
                                    </a>
                                {/snippet}
                            </Sidebar.MenuButton>
                        </Sidebar.MenuItem>
                        <Sidebar.MenuItem>
                            <Sidebar.MenuButton isActive={isRoute(route, 'account')}>
                                {#snippet child({ props })}
                                    <a {...props} href="/accounts" use:inertia>
                                        <UsersRoundIcon class="size-4 mr-2" />
                                        Accounts
                                    </a>
                                {/snippet}
                            </Sidebar.MenuButton>
                        </Sidebar.MenuItem>
                        <Sidebar.MenuItem>
                            <Sidebar.MenuButton isActive={isRoute(route, 'token')}>
                                {#snippet child({ props })}
                                    <a {...props} href="/tokens" use:inertia>
                                        <LockKeyholeIcon class="size-4 mr-2" />
                                        API Tokens
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
                            isActive={isRoute(route, 'profile')}
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
                        <Dropdown.Item onclick={() => (logout_dialog = true)}>
                            <LogOutIcon class="mr-2" />
                            Logout
                        </Dropdown.Item>
                    </Dropdown.Group>
                </Dropdown.Content>
            </Dropdown.Root>
        </Sidebar.Footer>
    </Sidebar.Root>
    <Sidebar.Inset class="peer-data-[variant=inset]:max-h-[calc(100svh-theme(spacing.4))]">
        {@render children()}
    </Sidebar.Inset>
</Sidebar.Provider>

<Dialog.Root
    bind:open={
        () => logout_dialog,
        (val) => {
            if (!logging_out.value) logout_dialog = val;
        }
    }
>
    <Dialog.Content>
        <Dialog.Content>
            <Dialog.Header>
                <Dialog.Title>Logout</Dialog.Title>
                <Dialog.Description>Are you sure you want to logout?</Dialog.Description>
            </Dialog.Header>
            <Dialog.Footer>
                <Dialog.Close
                    type="button"
                    class={buttonVariants({ variant: 'secondary' })}
                    disabled={loading.value}
                >
                    Cancel
                </Dialog.Close>
                <Button
                    variant="destructive"
                    onclick={() => {
                        logging_out.value = true;
                        router.visit(`/logout`, {
                            replace: false,
                            preserveState: false,
                            preserveScroll: false,
                            onFinish() {
                                logging_out.value = false;
                                logout_dialog = false;
                            }
                        });
                    }}
                    loading={logging_out.value}
                    disabled={loading.value}
                >
                    Logout
                </Button>
            </Dialog.Footer>
        </Dialog.Content>
    </Dialog.Content>
</Dialog.Root>
