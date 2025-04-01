import { untrack } from 'svelte';

export function debounce(
    action: () => void | Promise<void>,
    trigger: () => boolean,
    delay: number = 250
) {
    let timeout: ReturnType<typeof setTimeout> | null = null;
    let running = false;

    async function commit() {
        if (running) return;
        running = true;

        if (timeout) {
            clearTimeout(timeout);
            timeout = null;
        }

        try {
            await untrack(() => action());
        } finally {
            running = false;
        }
    }

    $effect(() => {
        if (!trigger()) return;
        return clearTimeout.bind(null, (timeout = setTimeout(commit, delay)));
    });

    return commit;
}
