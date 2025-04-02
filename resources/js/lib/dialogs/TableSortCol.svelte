<script lang="ts">
    import { ArrowUpDownIcon, ArrowDownAZIcon, ArrowUpAZIcon } from '@lucide/svelte';
    import { Button } from '$lib/components/ui/button';
    import type { Snippet } from 'svelte';
    import { noop, escapeRegExp } from 'lodash-es';
    import type { ClassValue } from 'clsx';

    let {
        children = undefined,
        name = undefined,
        column,
        value = $bindable(),
        onclick = noop,
        class: className = ''
    }: {
        children?: Snippet;
        name?: string;
        column: string;
        value: string;
        onclick(value: string): void;
        class?: string;
    } = $props();

    let regex = $derived(new RegExp(`^[+-]${escapeRegExp(column)}$`, 'i'));

    let active = $derived(regex.test(value));
    let asc = $derived(active ? value[0] === '+' : false);

    let Icon = $derived(active ? (asc ? ArrowDownAZIcon : ArrowUpAZIcon) : ArrowUpDownIcon);
</script>

<Button
    onclick={() => {
        value = active ? (asc ? `-${column}` : `+${column}`) : `+${column}`;
        onclick(value);
    }}
    variant="ghost"
    size="sm"
    class={className}
>
    <Icon class="w-4 h-4" />
    {#if children}
        {@render children()}
    {:else}
        {name}
    {/if}
</Button>
