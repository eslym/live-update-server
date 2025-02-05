<script lang="ts">
    import type { Root, RootContent } from 'hast';
    import { shiki } from '@/lib/shiki';
    import { Copy01Icon } from '@eslym/hugeicons-svelte';
    import parseInline from 'inline-style-parser';

    let { endpoint }: { endpoint: string } = $props();

    let code = $derived(`import { Capacitor, CapacitorHttp } from '@capacitor/core';
import { LiveUpdate } from '@capawesome/live-update';

const BUNDLES_ENDPOINT = ${JSON.stringify(endpoint)};

if (Capacitor.isNativePlatform()) {
    Promise.all([LiveUpdate.getVersionName(), LiveUpdate.getCurrentBundle()])
        .then(async ([{ versionName }, { bundleId }]) => {
            const url = new URL(BUNDLES_ENDPOINT);
            url.searchParams.set('platform', Capacitor.getPlatform());
            url.searchParams.set('version', versionName);
            const res = await CapacitorHttp.get({
                url: url.href,
                headers: {
                    Accept: 'application/json'
                },
                responseType: 'json'
            });

            if (res.status !== 200) return;
            if (res.data.name === bundleId) return;
            await LiveUpdate.downloadBundle({
                url: res.data.url,
                bundleId: res.data.name
            });
            await LiveUpdate.setNextBundle({
                bundleId: res.data.name
            });
            // Ask for user to reload here.
            // call LiveUpdate.reload() if user agrees.
        })
        .catch(() => {
            // Handle errors here.
            //
            // Bundle download failed, signature mismatch,
            // bundle already exists but update failed, etc.
        });
}
`);
    let highlighted = $derived(
        shiki.codeToHast(code, {
            lang: 'typescript',
            themes: {
                light: 'github-light',
                dark: 'github-dark'
            }
        })
    );

    const classes = {
        pre: 'h-0 min-h-0 flex-grow overflow-auto'
    } as Record<string, string>;

    const styleMap = {
        color: '--shiki-light',
        'background-color': '--shiki-light-bg',
        'font-style': '--shiki-light-font-style',
        'font-weight': '--shiki-light-font-weight',
        'text-decoration': '--shiki-light-text-decoration'
    } as Record<string, string>;

    function mergeProp(props: Record<string, any>, classes: string) {
        if (props.class) {
            props.class += ` ${classes}`;
        } else {
            props.class = classes;
        }
        if (props.style) {
            const parsed = parseInline(props.style);
            let transformed = '';
            for (const token of parsed) {
                if (token.type === 'declaration') {
                    token.property = styleMap[token.property] || token.property;
                    transformed += `${token.property}: ${token.value};`;
                }
            }
            props.style = transformed;
        }
        return props;
    }
</script>

{#snippet render_hast(token: RootContent | Root)}
    {#if token.type === 'element'}
        <svelte:element
            this={token.tagName}
            {...mergeProp(token.properties, classes[token.tagName])}
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

<div
    class="textarea max-w-5xl font-mono h-[600px] overflow-hidden w-full p-0 flex flex-col relative"
>
    {@render render_hast(highlighted)}
    <button
        type="button"
        class="btn btn-xs btn-secondary absolute right-6 top-2 pr-3"
        onclick={() => navigator.clipboard.writeText(code)}
    >
        <Copy01Icon size={12} class="inline-block mr-2" />
        Copy
    </button>
</div>

<style lang="postcss">
    * :global(code) {
        counter-reset: step;
        counter-increment: step 0;

        :global(.line) {
            @apply inline-block w-full pr-4;

            &:first-child::before {
                @apply pt-2;
            }

            &:last-child::before {
                @apply pb-2;
            }

            &::before {
                @apply border-r-2 border-gray-6/50 bg-gray-4 pl-0.5 pr-3 text-content3;
                position: sticky;
                left: 0;
                content: counter(step);
                counter-increment: step;
                width: 3rem;
                margin-right: 1rem;
                display: inline-block;
                text-align: right;
            }
        }
    }
</style>
