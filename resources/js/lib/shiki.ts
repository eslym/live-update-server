import { createHighlighterCore } from 'shiki/core';
import { createOnigurumaEngine } from 'shiki/engine/oniguruma';

let shiki: Awaited<ReturnType<typeof createHighlighterCore>> = undefined as any;

export { shiki };

export async function initShiki() {
    if (shiki) return;
    shiki = await createHighlighterCore({
        themes: [import('@shikijs/themes/github-light'), import('@shikijs/themes/github-dark')],
        langs: [import('@shikijs/langs/typescript')],
        engine: createOnigurumaEngine(import('shiki/wasm'))
    });
}
