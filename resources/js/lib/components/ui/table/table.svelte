<script lang="ts">
    import type { HTMLTableAttributes } from 'svelte/elements';
    import type { WithElementRef } from 'bits-ui';
    import { cn } from '$lib/utils.js';
    import type { ClassValue } from 'clsx';

    let {
        ref = $bindable(null),
        class: className,
        containerClass = null,
        children,
        ...restProps
    }: WithElementRef<HTMLTableAttributes> & {
        class?: ClassValue;
        containerClass?: ClassValue;
        children?: () => any;
    } = $props();
</script>

<div class={cn('relative w-full overflow-auto', containerClass)}>
    <table bind:this={ref} class={cn('w-full caption-bottom text-sm', className)} {...restProps}>
        {@render children?.()}
    </table>
</div>
