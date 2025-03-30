<script lang="ts" module>
    import type { WithElementRef } from 'bits-ui';
    import type { HTMLAnchorAttributes, HTMLButtonAttributes } from 'svelte/elements';
    import { type VariantProps, tv } from 'tailwind-variants';
    import { type ActionOptions, inertia } from '@/inertia';
    import { LoaderCircleIcon } from '@lucide/svelte';

    export const buttonVariants = tv({
        base: 'relative focus-visible:ring-ring inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0',
        variants: {
            variant: {
                default: 'bg-primary text-primary-foreground hover:bg-primary/90 shadow',
                destructive:
                    'bg-destructive text-destructive-foreground hover:bg-destructive/90 shadow-sm',
                outline:
                    'border-input bg-background hover:bg-accent hover:text-accent-foreground border shadow-sm',
                secondary: 'bg-secondary text-secondary-foreground hover:bg-secondary/80 shadow-sm',
                ghost: 'hover:bg-accent hover:text-accent-foreground',
                link: 'text-primary underline-offset-4 hover:underline'
            },
            size: {
                default: 'h-9 px-4 py-2',
                sm: 'h-8 rounded-md px-3 text-xs',
                lg: 'h-10 rounded-md px-8',
                icon: 'h-9 w-9'
            }
        },
        defaultVariants: {
            variant: 'default',
            size: 'default'
        }
    });

    export type ButtonVariant = VariantProps<typeof buttonVariants>['variant'];
    export type ButtonSize = VariantProps<typeof buttonVariants>['size'];

    export type ButtonProps = WithElementRef<HTMLButtonAttributes> &
        WithElementRef<HTMLAnchorAttributes> & {
            variant?: ButtonVariant;
            size?: ButtonSize;
            useInertia?: ActionOptions | boolean;
            loading?: boolean;
        };
</script>

<script lang="ts">
    import { cn } from '$lib/utils.js';
    import { noop } from 'lodash-es';

    let {
        class: className,
        variant = 'default',
        size = 'default',
        ref = $bindable(null),
        href = undefined,
        type = 'button',
        children,
        useInertia: visit = true,
        loading = false,
        disabled = false,
        ...restProps
    }: ButtonProps = $props();

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

{#if href}
    <a
        bind:this={ref}
        class={cn(buttonVariants({ variant, size }), className)}
        {href}
        {...restProps}
        use:visits.action={visits.options}
    >
        {@render children?.()}
    </a>
{:else}
    <button
        bind:this={ref}
        class={cn(buttonVariants({ variant, size }), className)}
        {type}
        disabled={disabled || loading}
        {...restProps}
    >
        {#if loading}
            <div class="absolute flex size-full place-items-center justify-center bg-inherit">
                <div class="flex animate-spin place-items-center justify-center">
                    <LoaderCircleIcon class="size-4" />
                </div>
            </div>
            <span class="sr-only">Loading</span>
        {/if}
        {@render children?.()}
    </button>
{/if}
