import axios from 'axios';

declare global {
    interface Window {
        axios: typeof axios;
    }

    type Timeout = ReturnType<typeof setTimeout>;

    interface Pagination<T> {
        current_page: number;
        data: T[];
        first_page_url: string;
        from: number;
        last_page: number;
        last_page_url: string;
        next_page_url: string;
        links: PaginationLink[];
        per_page: number;
        total: number;
    }

    interface PaginationResource<T> {
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

    interface PaginationLink {
        url: string | null;
        label: string;
        active: boolean;
    }
}
