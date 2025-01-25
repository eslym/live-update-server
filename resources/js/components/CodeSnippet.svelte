<script lang="ts">
    import { shiki } from '@/lib/shiki';
    import { dark } from '@/lib/config';
    import { Copy01Icon } from 'hugeicons-svelte';

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
    let theme = $derived(localStorage.getItem('theme'));
    let highlighted = $derived(
        shiki
            .codeToHtml(code, {
                lang: 'typescript',
                theme: `github-${theme ?? (dark.current ? 'dark' : 'light')}`
            })
            .replace('<pre class="', '<pre class="h-0 min-h-0 flex-grow overflow-auto ')
    );
</script>

<div
    class="textarea max-w-5xl font-mono h-[600px] overflow-hidden w-full p-0 flex flex-col relative"
>
    {@html highlighted}
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
