<script lang="ts">
    import { DatePicker as DatePickerPrimitive } from 'bits-ui';
    import * as Popover from '$lib/components/ui/popover';

    let { ...restProps }: DatePickerPrimitive.ContentProps = $props();
</script>

<DatePickerPrimitive.Content>
    {#snippet child({ props })}
        <Popover.Content {...props} {...restProps} class="w-max" />
    {/snippet}
</DatePickerPrimitive.Content>
