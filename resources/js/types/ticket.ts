import type { User } from './auth';
import type { Project } from './project';

export type TicketDetailContentText = {
    type: 'text';
    content: string;
};

export type TicketDetailContentJson = {
    type: 'json';
    content: Record<string, unknown>;
};

export type TicketDetail = {
    id: number;
    ticket_id: number;
    details: TicketDetailContentText | TicketDetailContentJson;
    created_at: string;
    updated_at: string;
};

export type Ticket = {
    id: number;
    project_id: number;
    created_by: number;
    assigned_to?: number;
    title: string;
    status: 'open' | 'in_progress' | 'closed';
    priority: 'low' | 'medium' | 'high';
    due_date?: string;
    closed_at?: string;
    created_at: string;
    updated_at: string;
    ticket_detail?: TicketDetail;
    project?: Project;
    creator?: User;
    recipient?: User;
};
