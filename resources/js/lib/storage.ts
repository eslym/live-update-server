import { storageProxy } from '@eslym/svelte-utility-stores/reactive-storage';

export const local = storageProxy(localStorage);
