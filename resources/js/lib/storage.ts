import { createSubscriber } from 'svelte/reactivity';

const notifiers = new WeakMap<Storage, Map<string, () => void>>();
const subscribers = new WeakMap<Storage, Map<string, () => void>>();

(window as any)._test = { notifiers, subscribers };

const orig = {
    getItem: Storage.prototype.getItem,
    setItem: Storage.prototype.setItem,
    removeItem: Storage.prototype.removeItem,
    clear: Storage.prototype.clear
};

function get_notifiers_map(storage: Storage): Map<string, () => void> {
    if (!notifiers.has(storage)) {
        notifiers.set(storage, new Map<string, () => void>());
    }
    return notifiers.get(storage) as Map<string, () => void>;
}

function get_subscribers_map(storage: Storage): Map<string, () => void> {
    if (!subscribers.has(storage)) {
        subscribers.set(storage, new Map<string, () => void>());
    }
    return subscribers.get(storage) as Map<string, () => void>;
}

Storage.prototype.getItem = function (key) {
    if (subscribers.get(this)?.has(key)) {
        subscribers.get(this)!.get(key)?.();
    } else {
        const instance = this;
        const subscribe = createSubscriber((update) => {
            const map = get_notifiers_map(instance);
            map.set(key, update);
            return () => map.delete(key);
        });
        get_subscribers_map(this).set(key, subscribe);
        subscribe();
    }
    return orig.getItem.call(this, key);
};

Storage.prototype.setItem = function (key, value) {
    const old = orig.getItem.call(this, key);
    if (old === value) return;
    orig.setItem.call(this, key, value);
    notifiers.get(this)?.get(key)?.();
};

Storage.prototype.removeItem = function (key) {
    const old = orig.getItem.call(this, key);
    if (old === null) return;
    orig.removeItem.call(this, key);
    if (notifiers.has(this)) {
        notifiers.get(this)?.get(key)?.();
    }
};

Storage.prototype.clear = function () {
    orig.clear.call(this);
    if (notifiers.has(this)) {
        notifiers.get(this)?.forEach((update) => update());
    }
};

window.addEventListener('storage', (event) => {
    if (!event.storageArea) return;
    if (event.key) {
        notifiers.get(event.storageArea)?.get(event.key)?.();
    } else {
        notifiers.get(event.storageArea)?.forEach((update) => update());
    }
});
