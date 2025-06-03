import React from 'react';

type StatusType = 'completed' | 'sent' | 'pending' | 'failed';

interface StatusBadgeProps {
    status: StatusType;
}

const statusConfig = {
    completed: {
        label: 'Completed',
        classes: 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
        dot: 'bg-green-600 dark:bg-green-500'
    },
    sent: {
        label: 'Sent',
        classes: 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300',
        dot: 'bg-amber-500 dark:bg-amber-400'
    },
    pending: {
        label: 'Pending',
        classes: 'bg-gray-100 dark:bg-gray-800/50 text-gray-800 dark:text-gray-300',
        dot: 'bg-gray-500 dark:bg-gray-400'
    },
    failed: {
        label: 'Failed',
        classes: 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
        dot: 'bg-red-500 dark:bg-red-400'
    }
};

export const StatusBadge: React.FC<StatusBadgeProps> = ({ status }) => {
    const config = statusConfig[status] || statusConfig.pending;

    return (
        <div className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${config.classes}`}>
            <span className={`w-1.5 h-1.5 ${config.dot} rounded-full mr-1.5`}></span>
            {config.label}
        </div>
    );
};

export default StatusBadge; 