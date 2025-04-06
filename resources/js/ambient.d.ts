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

    interface VersionRange {
        min?: number | null;
        max?: number | null;
    }

    interface VersionRequirements {
        android?: VersionRange | null;
        ios?: VersionRange | null;
    }

    interface Pagination<T, A extends Record<string, any> = {}> {
        data: T[];
        meta: {
            current_page: number;
            from: number;
            last_page: number;
            per_page: number;
            to: number;
            total: number;
        } & A;
        links: {
            first: string;
            last: string;
            prev: string | null;
            next: string | null;
        };
    }
}

export {};
