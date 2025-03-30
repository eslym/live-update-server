import {
    type FormDataConvertible,
    type Method,
    type Page,
    type Progress,
    router,
    type VisitOptions
} from '@inertiajs/core';
import { cloneDeep, isEqual } from 'lodash-es';
import { onDestroy } from 'svelte';

export type FormDataType = Record<string, FormDataConvertible>;
type FormOptions = Omit<VisitOptions, 'data'>;

export class FormValidationError extends Error {
    constructor(public errors: Record<string, string | undefined>) {
        super('The given data was invalid.');
    }
}

export class FormCanceledError extends Error {
    constructor() {
        super('The form was canceled.');
    }
}

export class FormProcessingError extends Error {
    constructor() {
        super('The form is still processing.');
    }
}

export interface InertiaForm<Data extends FormDataType> {
    readonly dirty: boolean;
    readonly processing: boolean;
    readonly progress: Progress | null;

    readonly wasSuccessful: boolean;
    readonly wasFailed: boolean;

    data: Data;
    default: Data;
    errors: Record<string, string | undefined>;

    transform(callback: (data: Data) => object): this;

    reset(...fields: (keyof Data)[]): this;

    handleSubmit(event: SubmitEvent & { currentTarget: HTMLFormElement }): void;

    submit(method: Method, url: string | URL, options?: FormOptions): Promise<Page>;

    get(url: string | URL, options?: FormOptions): Promise<Page>;

    post(url: string | URL, options?: FormOptions): Promise<Page>;

    put(url: string | URL, options?: FormOptions): Promise<Page>;

    patch(url: string | URL, options?: FormOptions): Promise<Page>;

    delete(url: string | URL, options?: FormOptions): Promise<Page>;

    cancel(): this;
}

export interface InertiaFormWithRemember<Data extends FormDataType> extends InertiaForm<Data> {
    restore(): this;

    remember(): this;
}

export function useForm<Data extends FormDataType>(defaultData: Data): InertiaForm<Data>;
export function useForm<Data extends FormDataType>(
    defaultData: Data,
    rememberKey: string
): InertiaFormWithRemember<Data>;
export function useForm<Data extends FormDataType>(defaultData: Data, rememberKey?: string) {
    let defaults = $state(cloneDeep(defaultData));
    let data = $state(cloneDeep(defaultData));
    let errors = $state<Record<string, string | undefined>>({});
    let processing = $state(false);
    let progress = $state<Progress | null>(null);

    let wasSuccessful = $state(false);
    let wasFailed = $state(false);

    let cancelToken: { cancel: () => void } | null = null;

    let transform: (data: Data) => object = (data: Data) => data;

    let dirty = $derived(!isEqual(data, defaults));

    function submit(method: Method, url: string | URL, options: FormOptions = {}) {
        if (processing) throw new FormProcessingError();
        form.remember?.();
        return new Promise<Page>((resolve, reject) => {
            const payload = transform(cloneDeep(data));
            const opts: Omit<VisitOptions, 'method'> = {
                ...options,
                onCancelToken(token) {
                    cancelToken = token;
                    options.onCancelToken?.(token);
                },
                onCancel() {
                    processing = false;
                    reject(new FormCanceledError());
                    options.onCancel?.();
                },
                onBefore(visit) {
                    wasSuccessful = wasFailed = false;
                    errors = {};
                    progress = null;
                    options.onBefore?.(visit);
                },
                onStart(visit) {
                    processing = true;
                    options.onStart?.(visit);
                },
                onProgress(prog) {
                    progress = prog ?? null;
                    options.onProgress?.(prog);
                },
                onSuccess(page) {
                    processing = false;
                    wasSuccessful = true;
                    wasFailed = false;
                    progress = null;
                    errors = {};
                    options.onSuccess?.(page);
                    resolve(page);
                },
                onError(err) {
                    errors = err;
                    processing = false;
                    wasSuccessful = false;
                    wasFailed = true;
                    progress = null;
                    options.onError?.(err);
                    reject(new FormValidationError(err));
                },
                onFinish(visit) {
                    processing = false;
                    progress = null;
                    cancelToken = null;
                    options.onFinish?.(visit);
                }
            };
            if (method === 'delete') {
                router.delete(url, {
                    ...opts,
                    data: payload as any
                });
            } else {
                router[method](url, payload as any, opts);
            }
        });
    }

    function handleSubmit(ev: SubmitEvent & { currentTarget: HTMLFormElement }) {
        ev.preventDefault();
        const el = ev.currentTarget;
        const method = (el.method.toLowerCase() || 'get') as Method;
        const url = el.action;
        form.submit(method, url);
    }

    const form: InertiaFormWithRemember<Data> = {
        get dirty() {
            return dirty;
        },
        get processing() {
            return processing;
        },
        get progress() {
            return progress;
        },
        get wasSuccessful() {
            return wasSuccessful;
        },
        get wasFailed() {
            return wasFailed;
        },
        get data() {
            return data;
        },
        set data(value: Data) {
            data = value;
        },
        get default() {
            return defaults;
        },
        set default(value: Data) {
            defaults = value;
        },
        get errors() {
            return errors;
        },
        set errors(value: Record<string, string | undefined>) {
            errors = value;
        },
        transform(callback: (data: Data) => object) {
            data = callback(data) as Data;
            return form;
        },
        reset(...fields: (keyof Data)[]) {
            if (fields.length) {
                fields.forEach((field) => (data[field] = cloneDeep(defaults[field])));
            } else {
                data = cloneDeep(defaults);
            }
            return form;
        },
        handleSubmit,
        submit,
        get: submit.bind(null, 'get'),
        post: submit.bind(null, 'post'),
        put: submit.bind(null, 'put'),
        patch: submit.bind(null, 'patch'),
        delete: submit.bind(null, 'delete'),
        cancel() {
            cancelToken?.cancel();
            return form;
        },
        remember: undefined!,
        restore: undefined!
    };

    if (rememberKey) {
        form.remember = () => {
            router.remember(cloneDeep({ data, errors }), rememberKey);
            return form;
        };
        form.restore = () => {
            const restored = router.restore(rememberKey) as
                | {
                      data: Data;
                      errors: Record<string, string | undefined>;
                  }
                | undefined;
            if (restored) {
                data = cloneDeep(restored.data);
                errors = restored.errors;
            }
            return form;
        };
        form.restore();
        onDestroy(form.remember);
    }

    return form;
}
