import { SvelteSet } from 'svelte/reactivity';
import { onDestroy } from 'svelte';
import { router } from '@inertiajs/core';

let value = $state(false);

let router_loading = $state(false);

const locals = new SvelteSet<{ value: boolean }>();

router.on('start', () => {
    router_loading = true;
});

router.on('finish', () => {
    router_loading = false;
});

function or(other: any) {
    return get_value() || Boolean(other);
}

function get_value() {
    let val = value || router_loading;
    for (const local of locals) {
        val ||= local.value;
    }
    return val;
}

function local(init = false) {
    let val = $state(init);
    const local = {
        get value() {
            return val;
        },
        set value(v: boolean) {
            val = v;
        },
        get or() {
            return or;
        }
    };
    locals.add(local);
    onDestroy(locals.delete.bind(locals, local));
    return local;
}

function derived(by: () => boolean) {
    let val = $derived.by(by);
    const local = {
        get value() {
            return val;
        },
        set value(v: boolean) {
            val = v;
        },
        get or() {
            return or;
        }
    };
    locals.add(local);
    onDestroy(locals.delete.bind(locals, local));
    return local;
}

export const loading = {
    get value() {
        return get_value();
    },
    set value(v: boolean) {
        value = v;
    },
    get or() {
        return or;
    },
    get local() {
        return local;
    },
    get derived() {
        return derived;
    }
};
