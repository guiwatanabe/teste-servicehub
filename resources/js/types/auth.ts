export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    user_profile?: UserProfile;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
    role: string;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};

export type UserProfile = {
    id: number;
    user_id: number;
    role: string;
    phone?: string;
    position?: string;
    address?: string;
    created_at: string;
    updated_at: string;
};
