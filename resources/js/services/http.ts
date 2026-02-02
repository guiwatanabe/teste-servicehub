import axios from 'axios';

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

const http = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
    },
});

axios.get('/sanctum/csrf-cookie').catch(() => {});

export default http;
