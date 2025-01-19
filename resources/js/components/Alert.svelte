<script lang="ts" module>
    let content = $state('');
    let show = $state(false);

    export function alert(message: string) {
        content = message;
        show = true;
    }
</script>

<input class="modal-state" id="modal-alert" type="checkbox" bind:checked={show}/>
<div class="modal">
    <label class="modal-overlay" for="modal-alert"></label>
    <div class="modal-content flex flex-col gap-5">
        <label for="modal-alert" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</label>
        <span class="mt-6">{content}</span>
        <div class="flex gap-3">
            <label for="modal-alert" class="btn btn-primary btn-block">OK</label>
        </div>
    </div>
</div>
