import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/react';
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
    const { totalPatients, formsSentToday, pendingForms, todaysAppointments } = usePage().props as {
        totalPatients: number;
        formsSentToday: number;
        pendingForms: number;
        todaysAppointments: number;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard">
                <meta name="description" content="View patient stats, pending forms today, and manage appointments in ClinicPing." />
            </Head>
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 bg-background text-foreground">
                {/* Stats Cards */}
                <DashboardStats
                    totalPatients={totalPatients}
                    formsSentToday={formsSentToday}
                    pendingForms={pendingForms}
                    todaysAppointments={todaysAppointments}
                />

                {/* Patient Table */}
                <PatientTable />
            </div>
        </AppLayout>
    );
}
