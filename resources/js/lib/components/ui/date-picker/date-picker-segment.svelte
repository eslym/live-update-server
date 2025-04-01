<script lang="ts" module>
    import { tv } from 'tailwind-variants';

    const defaultPartClass =
        'rounded px-1 py-1 hover:bg-muted focus:bg-muted focus:text-foreground aria-[valuetext=Empty]:text-muted-foreground';

    const segmentVariants = tv({
        base: 'p-1 outline-none ring-0 focus-visible:!ring-0 focus-visible:!ring-offset-0 [&:is([data-disabled]_*)]:pointer-events-none',
        variants: {
            part: {
                default: defaultPartClass,
                day: defaultPartClass,
                month: defaultPartClass,
                year: defaultPartClass,
                hour: defaultPartClass,
                minute: defaultPartClass,
                second: defaultPartClass,
                dayPeriod: defaultPartClass,
                timeZoneName: defaultPartClass,
                literal: 'text-muted-foreground'
            } satisfies Record<DatePickerPrimitive.SegmentProps['part'] | 'default', string>
        }
    });
</script>

<script lang="ts">
    import { DatePicker as DatePickerPrimitive } from 'bits-ui';
    import { cn } from '$lib/utils.js';

    let {
        ref = $bindable(null),
        class: className,
        part,
        ...restProps
    }: DatePickerPrimitive.SegmentProps = $props();
</script>

<DatePickerPrimitive.Segment
    bind:ref
    class={cn(segmentVariants({ part }), className)}
    {part}
    {...restProps}
/>
