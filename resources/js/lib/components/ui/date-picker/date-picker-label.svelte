<script lang="ts">
    import { DatePicker as DatePickerPrimitive } from 'bits-ui';
    import { Label } from '$lib/components/ui/label';

    type Props = { props: Record<string, unknown> };

    let {
        ref = $bindable(null),
        class: className,
        children = undefined,
        child = undefined,
        ...restProps
    }: DatePickerPrimitive.LabelProps = $props();

    let useChildren = $derived(child ? undefined : children);
    let useChild = $derived(useChildren ? undefined : (child ?? default_child));
</script>

{#snippet default_child({ props }: Props)}
    <Label {...props} />
{/snippet}

<DatePickerPrimitive.Label bind:ref children={useChildren} child={useChild} {...restProps} />
