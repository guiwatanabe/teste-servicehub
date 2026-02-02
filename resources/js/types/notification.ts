export type Notification = {
    id: string;
    type: string;
    notifiable_type: string;
    notifiable_id: number;
    data: {
        [key: string]: any;
    };
    read_at: string | null;
    created_at: string;
    updated_at: string;
};
