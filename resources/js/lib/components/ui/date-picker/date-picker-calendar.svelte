<script lang="ts">
    import { DatePicker as DatePickerPrimitive } from 'bits-ui';
    import * as Calendar from '$lib/components/ui/calendar';
    import { Button } from '$lib/components/ui/button';

    let { ref = $bindable(null), ...restProps }: DatePickerPrimitive.CalendarProps = $props();
</script>

<DatePickerPrimitive.Calendar bind:ref {...restProps}>
    {#snippet children({ months, weekdays })}
        <Calendar.Header>
            <Calendar.PrevButton />
            <Calendar.Heading />
            <Calendar.NextButton />
        </Calendar.Header>
        <Calendar.Months>
            {#each months as month (month)}
                <Calendar.Grid>
                    <Calendar.GridHead>
                        <Calendar.GridRow class="flex">
                            {#each weekdays as weekday, i (i)}
                                <Calendar.HeadCell>
                                    {weekday.slice(0, 2)}
                                </Calendar.HeadCell>
                            {/each}
                        </Calendar.GridRow>
                    </Calendar.GridHead>
                    <Calendar.GridBody>
                        {#each month.weeks as weekDates (weekDates)}
                            <Calendar.GridRow class="mt-2 w-full">
                                {#each weekDates as date (date)}
                                    <Calendar.Cell {date} month={month.value}>
                                        <Calendar.Day />
                                    </Calendar.Cell>
                                {/each}
                            </Calendar.GridRow>
                        {/each}
                    </Calendar.GridBody>
                </Calendar.Grid>
            {/each}
        </Calendar.Months>
    {/snippet}
</DatePickerPrimitive.Calendar>
