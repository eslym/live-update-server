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

    interface PaginationLink {
        url: string | null;
        label: string;
        active: boolean;
    }
}
