<script lang="ts" module>
    import { dateTimeFormat } from '@/lib/utils';
    import { parseAbsoluteToLocal } from '@internationalized/date';
    import type { Snippet } from 'svelte';
    import { SvelteSet } from 'svelte/reactivity';

    export type Contentable = string | { toString(): string } | Snippet | null | undefined;
    export type RequiredContentable = Exclude<Contentable, null | undefined>;

    export function booleans(): Record<string, boolean> {
        const data = $state({} as Record<string, boolean>);
        return new Proxy<Record<string, boolean>>(
            {},
            {
                get(target, prop: string | symbol) {
                    if (typeof prop === 'symbol') {
                        return Reflect.get(target, prop);
                    }
                    return data[prop] ?? false;
                },
                set(target, prop: string | symbol, value: boolean) {
                    if (typeof prop === 'symbol') {
                        return Reflect.set(target, prop, value);
                    }
                    if (value) {
                        data[prop] = true;
                    } else {
                        delete data[prop];
                    }
                    Reflect.set(target, prop, value);
                    return true;
                }
            }
        );
    }

    export { content, timestamp };
</script>

{#snippet content(content: Contentable)}
    {#if typeof content === 'function'}
        {@render content()}
    {:else if typeof content === 'string'}
        {content}
    {:else if content === null || content === undefined}{:else}
        {content.toString()}
    {/if}
{/snippet}

{#snippet timestamp(timestamp?: string | null)}
    {#if timestamp}
        {@const time = parseAbsoluteToLocal(timestamp)}
        {dateTimeFormat.format(time.toDate())}
    {:else}
        <span class="text-muted-foreground">(N/A)</span>
    {/if}
{/snippet}
