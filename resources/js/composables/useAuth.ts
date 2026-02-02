import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useAuth() {
    const page = usePage();

    const user = computed(() => page.props.auth?.user);

    const isAdmin = computed(() => page.props.auth?.role === 'admin');
    const isManager = computed(() => page.props.auth?.role === 'manager');
    const isEmployee = computed(() => page.props.auth?.role === 'employee');

    const hasRole = (roles: string | string[]) => {
        if (!page.props.auth.role) return false;
        const roleArray = Array.isArray(roles) ? roles : [roles];
        return roleArray.includes(page.props.auth.role);
    };

    const canAccess = (requiredRoles: string | string[]) => {
        return hasRole(requiredRoles);
    };

    return {
        user,
        isAdmin,
        isManager,
        isEmployee,
        hasRole,
        canAccess,
    };
}
