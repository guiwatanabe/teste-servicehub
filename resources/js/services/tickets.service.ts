import type { LaravelPagination } from '@/types/pagination';
import type { Ticket } from '@/types/ticket';
import http from './http';

type ApiItem<T> = { data: T };

export async function getTickets(
    perPage = 10,
    page = 1,
    filters?: { status?: string; priority?: string },
    orderBy?: string,
    orderDirection?: 'asc' | 'desc',
): Promise<LaravelPagination<Ticket>> {
    const params: Record<string, any> = {
        per_page: perPage,
        page,
        ...filters,
    };

    if (orderBy) params.order_by = orderBy;
    if (orderDirection) params.order_dir = orderDirection;

    const { data } = await http.get<LaravelPagination<Ticket>>(`/tickets`, {
        params,
    });

    return data;
}

export async function getTicket(id: number | string): Promise<Ticket> {
    const { data } = await http.get<ApiItem<Ticket>>(`/tickets/${id}`);
    return data.data;
}

export type CreateTicketInput = Omit<
    Ticket,
    'id' | 'created_at' | 'updated_at' | 'ticket_detail'
>;

export async function createTicket(
    ticketData: CreateTicketInput,
): Promise<Ticket> {
    const { data } = await http.post<ApiItem<Ticket>>('/tickets', ticketData);
    return data.data;
}

export type UpdateTicketInput = Partial<CreateTicketInput>;

export async function updateTicket(
    id: number | string,
    ticketData: UpdateTicketInput,
): Promise<Ticket> {
    const { data } = await http.put<ApiItem<Ticket>>(
        `/tickets/${id}`,
        ticketData,
    );
    return data.data;
}

export async function deleteTicket(id: number | string): Promise<void> {
    await http.delete(`/tickets/${id}`);
}

export async function closeTicket(id: number | string): Promise<Ticket> {
    const { data } = await http.post<ApiItem<Ticket>>(`/tickets/${id}/close`, {
        id: id,
    });
    return data.data;
}
