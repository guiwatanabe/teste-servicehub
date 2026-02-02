import type { Company } from '@/types/company';
import http from './http'

export async function getCompanies(): Promise<Company[]> {
    const { data } = await http.get<Company[]>('/companies');
    return data;
}