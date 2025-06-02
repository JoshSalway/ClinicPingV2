import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'SMS History', href: '/sms-history' },
];

export default function SmsHistory() {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="SMS History" />
      <div className="p-8">
        <h1 className="text-2xl font-bold mb-4">SMS History</h1>
        <p>This page will show all SMS messages sent.</p>
        {/* Table of SMS messages will go here */}
      </div>
    </AppLayout>
  );
} 