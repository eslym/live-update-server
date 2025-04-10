import './bootstrap';

import { createInertiaApp, page, router } from '@/inertia';
import { mount, tick } from 'svelte';
import { loadConfig } from '@/lib/config';
import Progress, { progress } from '$lib/components/LoadingProgress.svelte';
import { toast } from 'svelte-sonner';
import ThemeWatcher from '$lib/components/ThemeWatcher.svelte';
import { Toaster } from '$lib/components/ui/sonner';
import AlertDialog from '$lib/dialogs/AlertDialog.svelte';

const pages = import.meta.glob('./pages/**/*.svelte');

let timeout: Timeout | undefined = undefined;

router.on(
    'start',
    () =>
        (timeout = setTimeout(() => {
            progress.start();
        }, 250))
);

router.on('finish', () => {
    clearTimeout(timeout);
    progress.end();
});

router.on('progress', (event) => {
    if (event.detail.progress?.percentage) {
        progress.set(event.detail.progress.percentage);
    }
});

page.onUpdated((page) => {
    if (page?.props?.alert || page?.props?.toast) {
        router.replace({
            preserveState: true,
            props: (props) => {
                if (props.alert) {
                    const alertData = props.alert as {
                        title: string;
                        content: string;
                    };
                    mount(AlertDialog, {
                        target: document.body,
                        props: {
                            title: alertData.title,
                            description: alertData.content,
                            openOnMount: true
                        }
                    });
                    delete props.alert;
                }
                if (props.toast) {
                    const toastData = props.toast as {
                        type: 'success' | 'error' | 'info' | 'warning';
                        title: string;
                        description?: string;
                    };
                    toast[toastData.type ?? 'info'](toastData.title, {
                        description: toastData.description
                    });
                    delete props.toast;
                }
                return props;
            }
        });
    }
});

Promise.all([loadConfig()]).then(async () => {
    mount(ThemeWatcher, { target: document.body });
    mount(Progress, { target: document.body });
    mount(Toaster, { target: document.body });
    await createInertiaApp({
        resolve: async (name) => {
            const load = `./pages/${name}.svelte`;
            if (!pages[load]) {
                const error = `Page not found: ${name}`;
                tick().then(() => {
                    toast.error(error, {
                        description: `Please create "src/resources/js/pages/${name}.svelte"`
                    });
                });
                throw new Error(error);
            }
            return (await pages[load]()) as any;
        },
        setup({ el, App, props }) {
            mount(App, { target: el!, props });
        },
        progress: false
    });
});
