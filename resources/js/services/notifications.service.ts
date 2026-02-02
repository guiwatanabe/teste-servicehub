import http from './http';

export async function notificationRead(id: number | string): Promise<void> {
    await http.post(`/notifications/${id}/read`);
}
