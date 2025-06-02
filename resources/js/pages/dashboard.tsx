import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import DashboardStats from '@/components/dashboard-stats';
import AddPatientModal from '@/components/patients/AddPatientModal';
import PatientTable from '@/components/patients/patient-table';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 bg-background text-foreground">
                {/* Stats Cards */}
                <DashboardStats
                    totalPatients={5}
                    formsSentToday={3}
                    pendingForms={1}
                    todaysAppointments={0}
                />

                {/* Patient Table */}
                <PatientTable />
            </div>
        </AppLayout>
    );
}
