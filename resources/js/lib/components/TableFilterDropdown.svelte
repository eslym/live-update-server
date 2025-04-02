<script lang="ts">
    import * as Dropdown from '$lib/components/ui/dropdown-menu';
    import { buttonVariants } from '$lib/components/ui/button';
    import { cn } from '$lib/utils';
    import { CheckIcon, FunnelIcon } from '@lucide/svelte';

    type _keep = [typeof Dropdown];

    let {
        value = $bindable(),
        options,
        onchanged = undefined,
        hideLabel = false
    }: {
        value: string;
        options: { label: string; value: string }[];
        onchanged?: (value: string) => void;
        hideLabel?: boolean;
    } = $props();

    let hasLabel = $derived(!hideLabel && value);
</script>

<Dropdown.Root>
    <Dropdown.Trigger
        class={buttonVariants({
            variant: value ? 'default' : 'ghost',
            size: hasLabel ? 'sm' : 'icon'
        })}
    >
        {#if hasLabel && value}
            <span class="text-xs">
                {options.find((item) => item.value === value)?.label}
            </span>
        {/if}
        <FunnelIcon class={cn('size-4', hasLabel && 'ml-0.5')} />
    </Dropdown.Trigger>
    <Dropdown.Content>
        {#each options as item (item.value)}
            <Dropdown.Item
                onclick={() => {
                    value = item.value;
                    onchanged?.(item.value);
                }}
            >
                <CheckIcon class={cn('size-4', value !== item.value && 'opacity-0')} />
                {item.label}
            </Dropdown.Item>
        {/each}
    </Dropdown.Content>
</Dropdown.Root>
