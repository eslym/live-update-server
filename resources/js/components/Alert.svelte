<script lang="ts" module>
    import {nanoid} from "nanoid";
    import {createSubscriber, SvelteMap} from "svelte/reactivity";

    interface Alert {
        title?: string;
        content: string;
        actions?: {
            primary: string;
            secondary?: string;
        }
    }

    let alerts: Map<string, Alert & {
        show: boolean,
        result: boolean | null,
        resolve: (result: boolean | null) => void,
    }> = $state(new SvelteMap());

    export function alert(message: string | Alert) {
        if (typeof message === 'string') {
            message = {content: message};
        }
        const id = nanoid();
        return new Promise<boolean | null>(resolve => {
            let show = false;
            let notifier = () => {
            };
            const subscribe = createSubscriber((update) => {
                notifier = update;
                return () => notifier = () => {
                };
            });
            const obj = {
                ...message,
                result: null,
                resolve,
                get show() {
                    subscribe();
                    return show;
                },
                set show(value) {
                    if (value === show) return;
                    show = value;
                    notifier();
                }
            };
            alerts.set(id, obj);
            setTimeout(() => obj.show = true);
        });
    }

    function transitionEnd(ev: TransitionEvent & { currentTarget: HTMLDivElement }) {
        if (ev.target !== ev.currentTarget) return;
        const id = ev.currentTarget.dataset['modalId']!;
        const alert = alerts.get(id)!;
        if (!alert.show) {
            alert.resolve(alert.result);
            alerts.delete(id);
        }
    }
</script>

{#each alerts as [id, alert] (id)}
    <input class="modal-state" id="modal-alert-{id}" type="checkbox" bind:checked={alert.show}/>
    <div class="modal">
        <label class="modal-overlay" for="modal-alert-{id}"></label>
        <div class="modal-content flex flex-col gap-5" data-modal-id={id} ontransitionend={transitionEnd}>
            <label for="modal-alert-{id}" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</label>
            <h2 class="text-xl">{alert.title}</h2>
            <span>{alert.content}</span>
            <div class="flex gap-3">
                <button onclick={() => {
                    alert.result = true;
                    alert.show = false;
                }} class="btn btn-primary btn-block">{alert.actions?.primary ?? 'OK'}</button>
                {#if alert.actions?.secondary}
                    <button onclick={()=>{
                        alert.result = false;
                        alert.show = false;
                    }} class="btn btn-block">{alert.actions?.secondary}</button>
                {/if}
            </div>
        </div>
    </div>
{/each}
