import { createSubscriber } from 'svelte/reactivity';
import { on } from 'svelte/events';
import { router } from '@inertiajs/svelte';

const subscribe = createSubscriber((update) => {
    return on(window, 'hashchange', update);
});

export const fragment = {
    get value() {
        subscribe();
        return window.location.hash;
    },
    set value(value) {
        if (!value.startsWith('#')) {
            value = '#' + value;
        }
        if (value !== window.location.hash) {
            const newUrl = new URL(value, window.location.href);
            router.push({
                url: newUrl.href
            });
            window.dispatchEvent(new HashChangeEvent('hashchange'));
        }
    }
};

export function replaceHash(value: string) {
    if (!value.startsWith('#')) {
        value = '#' + value;
    }
    if (value !== window.location.hash) {
        const newUrl = new URL(value, window.location.href);
        router.replace({
            url: newUrl.href
        });
        window.dispatchEvent(new HashChangeEvent('hashchange'));
    }
}

document.body.addEventListener('click', ((event: MouseEvent & { target: HTMLAnchorElement }) => {
    if (
        event.defaultPrevented ||
        event.button !== 0 ||
        event.ctrlKey ||
        event.metaKey ||
        event.shiftKey ||
        event.target.tagName !== 'A' ||
        event.target.target === '_blank'
    )
        return;
    const url = new URL(event.target.href);
    if (
        url.origin === window.location.origin &&
        url.pathname === window.location.pathname &&
        url.search === window.location.search &&
        url.hash !== window.location.hash
    ) {
        event.preventDefault();
        fragment.value = url.hash;
    }
}) as any);
