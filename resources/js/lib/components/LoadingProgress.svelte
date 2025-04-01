<script lang="ts" module>
    import { Tween } from 'svelte/motion';
    import { fade } from 'svelte/transition';
    import { sineInOut } from 'svelte/easing';

    let show = $state(false);
    let ending = false;

    const val = new Tween(0, { duration: 250, easing: sineInOut });

    function trickle() {
        if (!show || ending) return;
        const rest = 100 - val.current;
        const inc = ((0.5 + Math.random()) / 1.5) * (rest / 3);
        val.set(val.current + inc, { duration: 1500 + Math.random() * 1000 }).then(trickle);
    }

    function start() {
        if (show) return;
        show = true;
        val.set(0, { duration: 0 });
        val.set(25, { duration: 1500 + Math.random() * 1000 }).then(trickle);
    }

    function set(percent: number) {
        if (ending || percent < val.target) return;
        val.set(percent, { duration: 250 }).then(trickle);
    }

    function end() {
        ending = true;
        val.set(100, { duration: 250 }).then(() => {
            show = false;
            ending = false;
        });
    }

    export const progress = {
        start,
        set,
        end
    };
</script>

{#if show}
    <div
        out:fade={{ duration: 250 }}
        class="progress"
        style:width="{val.current.toFixed(2)}vw"
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
