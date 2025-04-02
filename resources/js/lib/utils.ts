import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { DateFormatter } from '@internationalized/date';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export const dateTimeFormat = new DateFormatter('en', {
    hour: 'numeric',
    minute: '2-digit',
    second: '2-digit',
    hour12: false,
    month: 'short',
    day: 'numeric',
    year: 'numeric'
});
