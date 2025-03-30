/// <reference types="bun-types" />

import { createHighlighterCore } from 'shiki/core';
import { createOnigurumaEngine } from 'shiki/engine/oniguruma';

import parse from 'inline-style-parser';
import type { Element } from 'hast';
import type { ShikiTransformer } from 'shiki';

const map = {
    '--shiki-dark': 'color',
    '--shiki-dark-bg': 'background-color'
} as Record<string, string>;

function transformStyles(element: Element) {
    if (!element.properties.style) return;
    const styles = parse(element.properties.style as string);
    const props: Record<string, string> = {};
    const dark: Record<string, string> = {};
    for (const style of styles) {
        if (style.type === 'comment') continue;
        if (style.property.startsWith('--shiki-')) {
            if (style.property in map) {
                dark[map[style.property]] = style.value;
            }
            continue;
        }
        props[style.property] = style.value;
    }
    for (const key of Object.keys(dark)) {
        if (!props[key]) continue;
        props[key] = `light-dark(${props[key]}, ${dark[key]})`;
    }
    element.properties.style = Object.entries(props)
        .map(([key, value]) => `${key}: ${value}`)
        .join('; ');
}

export const dualThemeLightDark: ShikiTransformer = {
    name: 'dual-theme-light-dark',
    pre: transformStyles,
    code: transformStyles,
    line: transformStyles,
    span: transformStyles
};

const shiki = await createHighlighterCore({
    themes: [
        import('@shikijs/themes/catppuccin-latte'),
        import('@shikijs/themes/catppuccin-mocha')
    ],
    langs: [import('@shikijs/langs/typescript')],
    engine: createOnigurumaEngine(import('shiki/wasm'))
});

const code = await Bun.file('resources/example/live-update.ts').text();
const hast = shiki.codeToHast(code, {
    lang: 'typescript',
    themes: {
        light: 'catppuccin-latte',
        dark: 'catppuccin-mocha'
    },
    transformers: [dualThemeLightDark]
});
await Bun.write('resources/js/example/live-update.json', JSON.stringify(hast));

export {};
