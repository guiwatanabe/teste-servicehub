import type { Company } from "./company";
import type { Ticket } from "./ticket";

export type Project = {
    id: number;
    title: string;
    details?: string;
    company_id: number;
    created_at: string;
    updated_at: string;
    tickets?: Ticket[];
    tickets_total?: number;
    tickets_open?: number;
    tickets_in_progress?: number;
    tickets_closed?: number;
    company?: Company;
};