<script lang="ts">
    import type { Root, RootContent } from 'hast';
    import type { ClassValue } from 'clsx';
    import { cn } from '$lib/utils';

    let { token, styles = {} }: { token: Root; styles?: Record<string, ClassValue> } = $props();

    function mergeProp(props: Record<string, any>, classes?: ClassValue) {
        if (props.class) {
            props.class += cn(props.class, classes);
        } else {
            props.class = cn(classes);
        }
        return props;
    }
</script>

{#snippet render_hast(token: RootContent | Root)}
    {#if token.type === 'element'}
        <svelte:element
            this={token.tagName}
            {...mergeProp(token.properties, styles[token.tagName])}
        >
            {#each token.children as child}
                {@render render_hast(child)}
            {/each}
        </svelte:element>
    {:else if token.type === 'text'}
        {token.value}
    {:else if token.type === 'root'}
        {#each token.children as child}
            {@render render_hast(child)}
        {/each}
    {/if}
{/snippet}

{@render render_hast(token)}
