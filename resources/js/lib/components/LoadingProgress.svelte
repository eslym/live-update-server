<script lang="ts" module>
    import { Tween } from 'svelte/motion';
    import { fade } from 'svelte/transition';

    let show = $state(false);
    export const progress = Object.assign(new Tween(0, { duration: 250 }), {
        get show() {
            return show;
        },
        set show(value) {
            show = value;
        }
    });
</script>

{#if show}
    <div
        out:fade={{ duration: 250 }}
        class="progress"
        style:width="{progress.current.toFixed(2)}vw"
    ></div>
{/if}

<style lang="postcss">
    .progress {
        @apply bg-accent-foreground;
        z-index: 999999;
        position: fixed;
        top: 0;
        left: 0;
        width: 0;
        height: 2px;
        pointer-events: none;
    }
</style>
