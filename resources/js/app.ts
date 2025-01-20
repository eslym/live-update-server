import './bootstrap';

import {createInertiaApp, page, router} from '@inertiajs/svelte'
import {mount} from 'svelte'
import NProgress from 'nprogress';
import Alert, {alert} from "@/components/Alert.svelte";

const pages = import.meta.glob('./pages/**/*.svelte')

router.on('start', () => NProgress.start());
router.on('finish', (event) => {
    if (event.detail.visit.completed) {
        NProgress.done()
    } else if (event.detail.visit.interrupted) {
        NProgress.set(0)
    } else if (event.detail.visit.cancelled) {
        NProgress.done()
        NProgress.remove()
    }
});

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
