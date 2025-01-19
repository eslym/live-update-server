import './bootstrap';

import {createInertiaApp, page} from '@inertiajs/svelte'
import {mount} from 'svelte'
import Alert, {alert} from "@/components/Alert.svelte";

const pages = import.meta.glob('./pages/**/*.svelte', {eager: true})

page.subscribe(($page) => {
    if ($page?.props?.alert) {
        alert($page.props.alert as any);
    }
});

createInertiaApp({
    resolve: name => {
        return pages[`./pages/${name}.svelte`] as any
    },
    setup({el, App, props}) {
        mount(App, {target: el!, props})
    },
}).then(() => {
    mount(Alert, {target: document.body});
});
