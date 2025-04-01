<script lang="ts">
    import { DatePicker as DatePickerPrimitive } from 'bits-ui';
    import { cn } from '$lib/utils.js';
    import DatePickerSegment from './date-picker-segment.svelte';
    import DatePickerTrigger from './date-picker-trigger.svelte';

    type SegmentPart = DatePickerPrimitive.SegmentProps['part'];
    type SegmentSnippet = { part: SegmentPart; value: string };

    let {
        ref = $bindable(null),
        class: className,
        children = undefined,
        withoutTrigger = false,
        ...restProps
    }: DatePickerPrimitive.InputProps & {
        withoutTrigger?: boolean;
    } = $props();
</script>

{#snippet default_children({ segments }: { segments: SegmentSnippet[] })}
    {#each segments as { part, value }}
        <div class="inline-block select-none">
            <DatePickerSegment {part}>{value}</DatePickerSegment>
        </div>
    {/each}
    {#if !withoutTrigger}
        <DatePickerTrigger />
    {/if}
{/snippet}

<DatePickerPrimitive.Input
    bind:ref
    children={restProps.child ? undefined : (children ?? default_children)}
    class={cn(
        'border-input focus-within:ring-ring h-9 w-full rounded-md border bg-transparent pl-3 py-1 text-base shadow-sm transition-colors focus-within:outline-none focus-within:ring-1 data-[disabled]:cursor-not-allowed data-[disabled]:opacity-50 md:text-sm flex flex-row items-center tracking-[0.01rem]',
        withoutTrigger && 'pr-3',
        className
    )}
    {...restProps}
/>
