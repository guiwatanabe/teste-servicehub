export const useTicketStyles = () => {
    const getPriorityColor = (priority: string) => {
        switch (priority) {
            case 'low':
                return 'bg-green-300 dark:bg-green-800';
            case 'medium':
                return 'bg-yellow-300 dark:bg-yellow-600';
            case 'high':
                return 'bg-red-400 dark:bg-red-500';
            default:
                return 'bg-gray-600';
        }
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'open':
                return 'bg-green-300 dark:bg-green-800';
            case 'in_progress':
                return 'bg-sky-300 dark:bg-sky-600';
            case 'closed':
                return 'bg-gray-300 dark:bg-gray-600';
            default:
                return 'bg-gray-600';
        }
    };

    const getDateColor = (status: string, dueDate: string | null) => {
        if (!dueDate) {
            return 'text-muted-foreground';
        }

        const due = new Date(dueDate);
        const now = new Date();

        if (status !== 'closed' && due < now) {
            return 'text-red-500';
        }

        return 'text-white';
    };

    return {
        getPriorityColor,
        getStatusColor,
        getDateColor,
    };
};
