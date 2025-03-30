import { createSubscriber, MediaQuery } from 'svelte/reactivity';

let notify = () => {};
const subscribe = createSubscriber((update) => {
    notify = update;
    return () => (notify = () => {});
});
const src = {
    TIMEZONE: Intl.DateTimeFormat().resolvedOptions().timeZone
};
export const config = new Proxy(src, {
    get(target: {}, p: string | symbol, receiver: any): any {
        subscribe();
        return Reflect.get(target, p, receiver);
    }
}) as {
    TIMEZONE: string;
    APP_ENV: string;
    APP_NAME: string;
    ENFORCE_2FA: boolean;
};

export const dark = new MediaQuery('prefers-color-scheme: dark');

export async function loadConfig() {
    const res = await fetch('/config.json');
    Object.assign(src, await res.json());
    notify();
}
