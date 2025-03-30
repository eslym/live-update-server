<script lang="ts">
    import { Label as LabelPrimitive } from 'bits-ui';
    import { cn } from '$lib/utils.js';

    let {
        ref = $bindable(null),
        class: className,
        required = false,
        ...restProps
    }: LabelPrimitive.RootProps & {
        required?: boolean;
    } = $props();
</script>

<LabelPrimitive.Root
    bind:ref
    class={cn(
        'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70',
        required && "after:content-['*'] after:text-rose-600 dark:after:text-red-400 after:ml-0.5",
        className
    )}
    {...restProps}
/>
