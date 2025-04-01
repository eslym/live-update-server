import axios from 'axios';
import '@/lib/storage';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['X-Client-Timezone'] =
    Intl.DateTimeFormat().resolvedOptions().timeZone;
