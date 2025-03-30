<script lang="ts">
    import type { HTMLAnchorAttributes } from 'svelte/elements';
    import type { Snippet } from 'svelte';
    import type { WithElementRef } from 'bits-ui';
    import { cn } from '$lib/utils.js';
    import { type ActionOptions, inertia } from '@/inertia';
    import { noop } from 'lodash-es';

    let {
        ref = $bindable(null),
        class: className,
        href = undefined,
        child,
        children,
        useInertia: visit = true,
        ...restProps
    }: WithElementRef<HTMLAnchorAttributes> & {
        child?: Snippet<
            [
                {
                    props: HTMLAnchorAttributes;
                }
            ]
        >;
        useInertia?: ActionOptions | boolean;
    } = $props();

    const attrs = $derived({
        class: cn('hover:text-foreground transition-colors', className),
        href,
        ...restProps
    });

    let visits = $derived.by(() => {
        if (visit === false) {
            return {
                action: noop,
                options: undefined
            };
        }
        if (visit === true) {
            return {
                action: inertia,
                options: {}
            };
        }
        return {
            action: inertia,
            options: visit
        };
    });
</script>

{#if child}
    {@render child({ props: attrs })}
{:else}
    <a bind:this={ref} {...attrs} use:visits.action={visits.options}>
        {@render children?.()}
    </a>
{/if}
