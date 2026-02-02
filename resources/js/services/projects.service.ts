import type { LaravelPagination } from '@/types';
import type { Project } from '@/types/project';
import type { Ticket } from '@/types/ticket';
import http from './http'

export async function getProjects(): Promise<Project[]> {
    const { data } = await http.get<Project[]>('/projects');
    return data;
}

export async function getProjectTickets(
    projectId: number | string,
    perPage = 10,
    page = 1,
): Promise<LaravelPagination<Ticket>> {
    const { data } = await http.get<LaravelPagination<Ticket>>(
        `/projects/${projectId}/tickets`,
        { params: { per_page: perPage, page } },
    );

    return data;
}