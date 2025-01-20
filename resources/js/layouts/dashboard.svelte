<script lang="ts">
    import {inertia} from "@inertiajs/svelte";
    import {DashboardSquare02Icon, Door01Icon, LockKeyIcon, Menu01Icon, UserIcon} from 'hugeicons-svelte';

    interface Props {
        children?: import('svelte').Snippet;
        route: string | null;
    }

    let {children, route}: Props = $props();

    let group = $derived(route?.split('.')?.[0]);
</script>

<div class="flex flex-row md:gap-10">
    <div class="md:w-full md:max-w-[18rem]">
        <input type="checkbox" id="sidebar-mobile-fixed" class="sidebar-state"/>
        <label for="sidebar-mobile-fixed" class="sidebar-overlay"></label>
        <aside
            class="sidebar sidebar-fixed-left sidebar-mobile h-full justify-start max-md:fixed max-md:-translate-x-full"
        >
            <section class="sidebar-content">
                <nav class="menu rounded-md">
                    <section class="menu-section px-4">
                        <ul class="menu-items">
                            <li class="contents">
                                <a
                                    href="/"
                                    class="menu-item"
                                    class:menu-active={group === 'project'}
                                    use:inertia
                                >
                                    <DashboardSquare02Icon
                                        class="h-5 w-5 opacity-75"
                                    />
                                    <span>Projects</span>
                                </a>
                            </li>
                            <li class="contents">
                                <a
                                    href="/profile"
                                    class="menu-item"
                                    class:menu-active={group === 'profile'}
                                    use:inertia
                                >
                                    <UserIcon class="h-5 w-5 opacity-75"/>
                                    <span>Profile</span>
                                </a>
                            </li>
                            <li class="contents">
                                <a
                                    href="/tokens"
                                    class="menu-item"
                                    class:menu-active={group === 'token'}
                                    use:inertia
                                >
                                    <LockKeyIcon class="h-5 w-5 opacity-75"/>
                                    <span>Tokens</span>
                                </a>
                            </li>
                        </ul>
                    </section>
                </nav>
            </section>
            <section class="sidebar-footer justify-end bg-gray-2 py-2">
                <div class="divider my-0"></div>
                <nav class="menu rounded-md">
                    <section class="menu-section px-4">
                        <ul class="menu-items">
                            <li class="contents">
                                <label for="modal-logout" class="menu-item">
                                    <Door01Icon
                                        class="h-5 w-5 opacity-75"
                                    />
                                    <span>Logout</span>
                                </label>
                            </li>
                        </ul>
                    </section>
                </nav>
            </section>
        </aside>
    </div>
    <div class="flex w-0 flex-grow flex-col p-4 gap-2 min-h-dvh">
        <div class="w-fit sticky top-2 z-30 pointer-events-none">
            <label for="sidebar-mobile-fixed" class="btn btn-circle md:hidden pointer-events-auto">
                <Menu01Icon/>
            </label>
        </div>
        {@render children?.()}
    </div>
</div>

<input class="modal-state" id="modal-logout" type="checkbox"/>
<div class="modal">
    <label class="modal-overlay" for="modal-logout"></label>
    <div class="modal-content flex flex-col gap-5">
        <label for="modal-logout" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</label>
        <h2 class="text-xl">Are you sure?</h2>
        <span>You are about to logout from the system.</span>
        <div class="flex gap-3">
            <a href="/logout" use:inertia class="btn btn-primary btn-block">Confirm</a>

            <label for="modal-logout" class="btn btn-block">Cancel</label>
        </div>
    </div>
</div>
