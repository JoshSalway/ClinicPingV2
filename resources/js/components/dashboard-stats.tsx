import React from 'react';

interface DashboardStatsProps {
  totalPatients: number;
  formsSentToday: number;
  pendingForms: number;
  todaysAppointments: number;
}

export default function DashboardStats({
  totalPatients,
  formsSentToday,
  pendingForms,
  todaysAppointments,
}: DashboardStatsProps) {
  return (
    <div className="grid auto-rows-min gap-4 md:grid-cols-4">
      {/* Total Patients */}
      <div className="rounded-lg bg-white dark:bg-neutral-900 text-gray-900 dark:text-white border border-gray-200 dark:border-neutral-800">
        <div className="p-6">
          <div className="flex items-center">
            <div className="w-12 h-12 bg-blue-100 dark:bg-blue-800 rounded-lg flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-blue-600 dark:text-blue-300 h-6 w-6">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
              </svg>
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600 dark:text-gray-300">Total Patients</p>
              <p className="text-2xl font-bold text-gray-900 dark:text-white">{totalPatients}</p>
            </div>
          </div>
        </div>
      </div>
      {/* Forms Sent Today */}
      <div className="rounded-lg bg-white dark:bg-neutral-900 text-gray-900 dark:text-white border border-gray-200 dark:border-neutral-800">
        <div className="p-6">
          <div className="flex items-center">
            <div className="w-12 h-12 bg-yellow-100 dark:bg-yellow-800 rounded-lg flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-yellow-600 dark:text-yellow-300 h-6 w-6">
                <path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"></path>
                <path d="m21.854 2.147-10.94 10.939"></path>
              </svg>
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600 dark:text-gray-300">Forms Sent Today</p>
              <p className="text-2xl font-bold text-gray-900 dark:text-white">{formsSentToday}</p>
            </div>
          </div>
        </div>
      </div>
      {/* Pending Forms */}
      <div className="rounded-lg bg-white dark:bg-neutral-900 text-gray-900 dark:text-white border border-gray-200 dark:border-neutral-800">
        <div className="p-6">
          <div className="flex items-center">
            <div className="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-gray-600 dark:text-gray-200 h-6 w-6">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600 dark:text-gray-300">Pending forms today</p>
              <p className="text-2xl font-bold text-gray-900 dark:text-white">{pendingForms}</p>
            </div>
          </div>
        </div>
      </div>
      {/* Today's Appointments */}
      <div className="rounded-lg bg-white dark:bg-neutral-900 text-gray-900 dark:text-white border border-gray-200 dark:border-neutral-800">
        <div className="p-6">
          <div className="flex items-center">
            <div className="w-12 h-12 bg-purple-100 dark:bg-purple-800 rounded-lg flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-purple-600 dark:text-purple-300 h-6 w-6">
                <path d="M8 2v4"></path>
                <path d="M16 2v4"></path>
                <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                <path d="M3 10h18"></path>
              </svg>
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600 dark:text-gray-300">Today's Appointments</p>
              <p className="text-2xl font-bold text-gray-900 dark:text-white">{todaysAppointments}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
} 