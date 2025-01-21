import './bootstrap';

import {createInertiaApp, page, router} from '@inertiajs/svelte'
import {mount} from 'svelte'
import NProgress from 'nprogress';
import Alert, {alert} from "@/components/Alert.svelte";

const pages = import.meta.glob('./pages/**/*.svelte')

let timeout: number | undefined = undefined;

router.on('start', () => timeout = setTimeout(NProgress.start, 250));

router.on('finish', (event) => {
    clearTimeout(timeout);
    if (event.detail.visit.completed) {
        NProgress.done()
    } else if (event.detail.visit.interrupted) {
        NProgress.set(0)
    } else if (event.detail.visit.cancelled) {
        NProgress.done()
        NProgress.remove()
    }
});

router.on('progress', (event) => {
    if (event.detail.progress?.percentage) {
        NProgress.set((event.detail.progress.percentage / 100) * 0.9)
    }
})

page.subscribe(($page) => {
    if ($page?.props?.alert) {
        alert($page.props.alert as any).then(() => {
        });
    }
});

createInertiaApp({
    resolve: async (name) => {
        return await pages[`./pages/${name}.svelte`]() as any
    },
    setup({el, App, props}) {
        mount(App, {target: el!, props})
    },
    progress: false,
}).then(() => {
    mount(Alert, {target: document.body});
});
