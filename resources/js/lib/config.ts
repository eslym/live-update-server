import {createSubscriber} from "svelte/reactivity";

let notify = () => {
};
const subscribe = createSubscriber((update) => {
    notify = update;
    return () => notify = () => {
    };
});
const src = {};
export const config = new Proxy(src, {
    get(target: {}, p: string | symbol, receiver: any): any {
        subscribe();
        return Reflect.get(target, p, receiver);
    }
}) as {
    APP_NAME: string;
    ENFORCE_2FA: boolean;
};

export async function loadConfig() {
    const res = await fetch('/config.json');
    Object.assign(src, await res.json());
    notify();
}
