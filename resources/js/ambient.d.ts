import axios from 'axios';

declare global {
    interface Window {
        axios: typeof axios;
    }

    type Timeout = ReturnType<typeof setTimeout>;

    interface User {
        id: number;
        nanoid: string;
        name: string;
        email: string;
        ['2fa_enabled']: boolean;
    }

    interface Pagination<T> {
        data: T[];
        meta: {
            current_page: number;
            from: number;
            last_page: number;
            per_page: number;
            to: number;
            total: number;
        };
        links: {
            first: string;
            last: string;
            prev: string | null;
            next: string | null;
        };
    }
}

export {};
