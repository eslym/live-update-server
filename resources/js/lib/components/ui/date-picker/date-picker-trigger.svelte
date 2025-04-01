<script lang="ts">
    import { DatePicker as DatePickerPrimitive } from 'bits-ui';
    import { CalendarIcon } from '@lucide/svelte';
    import { buttonVariants } from '../button';
    import { cn } from '$lib/utils';

    let {
        ref = $bindable(null),
        class: className,
        children = undefined,
        ...restProps
    }: DatePickerPrimitive.TriggerProps = $props();
</script>

{#snippet default_children()}
    <CalendarIcon class="size-6" />
{/snippet}

<DatePickerPrimitive.Trigger
    bind:ref
    children={restProps.child ? undefined : (children ?? default_children)}
    class={cn(
        buttonVariants({ variant: 'ghost', size: 'icon' }),
        'ml-auto outline-none focus-visible:ring-0',
        className
    )}
    {...restProps}
/>
