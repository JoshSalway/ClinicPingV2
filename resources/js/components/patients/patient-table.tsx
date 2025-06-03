import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import AddPatientModal from '@/components/patients/AddPatientModal';
import StatusBadge from './status-badge';

type StatusType = 'completed' | 'sent' | 'pending' | 'failed';

interface Patient {
  id: number;
  first_name: string;
  last_name: string;
  phone: string;
  email?: string;
  appointment_date?: string;
  appointment_time?: string;
  status: StatusType;
}

interface PatientApiResponse {
  data: Patient[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export default function PatientTable() {
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('all');
  const [sortBy, setSortBy] = useState('name');
  const [page, setPage] = useState(1);

  const { data, isLoading, isError } = useQuery<PatientApiResponse>({
    queryKey: ['patients', { search, status, sortBy, page }],
    queryFn: async () => {
      const params = new URLSearchParams({ search, status, sortBy, page: String(page) });
      const res = await fetch(`/api/patients?${params}`);
      if (!res.ok) throw new Error('Failed to fetch');
      return res.json();
    },
  });

  const safeData: PatientApiResponse = data || { data: [], current_page: 1, last_page: 1, per_page: 10, total: 0 };

  return (
    <div className="border border-gray-200 dark:border-neutral-800 rounded-2xl shadow-sm bg-white dark:bg-neutral-900">
      {/* Header */}
      <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-neutral-800">
        <h2 className="text-lg font-semibold text-gray-900 dark:text-white">Patient Management</h2>
        <AddPatientModal />
      </div>
      {/* Search and Filter Controls */}
      <div className="px-6 py-4 border-b border-gray-200 dark:border-neutral-800 bg-gray-50 dark:bg-transparent flex flex-col md:flex-row gap-4 md:items-center md:justify-between">
        <Input
          placeholder="Search by name or phone..."
          value={search}
          onChange={e => { setSearch(e.target.value); setPage(1); }}
          className="md:w-64"
        />
        <div className="flex gap-2">
          <Select value={status} onValueChange={v => { setStatus(v); setPage(1); }}>
            <SelectTrigger className="w-32">
              <SelectValue placeholder="All Status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Status</SelectItem>
              <SelectItem value="sent">Form Sent</SelectItem>
              <SelectItem value="pending">Pending</SelectItem>
              <SelectItem value="completed">Completed</SelectItem>
              <SelectItem value="failed">Failed</SelectItem>
            </SelectContent>
          </Select>
          <Select value={sortBy} onValueChange={v => { setSortBy(v); setPage(1); }}>
            <SelectTrigger className="w-40">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="name">Sort by Name</SelectItem>
              <SelectItem value="appointment_date">Sort by Appointment</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>
      {/* Table */}
      <div className="overflow-x-auto">
        {isLoading ? (
          <div className="p-8 text-center text-gray-500 dark:text-gray-400">Loading...</div>
        ) : isError ? (
          <div className="p-8 text-center text-red-500">Failed to load patients.</div>
        ) : (
          <table className="min-w-full divide-y divide-gray-200 dark:divide-neutral-800">
            <thead className="bg-gray-50 dark:bg-transparent">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Patient</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Contact</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Appointment</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody className="bg-white dark:bg-neutral-900 divide-y divide-gray-200 dark:divide-neutral-800">
              {safeData.data.length === 0 ? (
                <tr>
                  <td colSpan={5} className="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No patients found.</td>
                </tr>
              ) : (
                safeData.data.map((patient: Patient) => (
                  <tr key={patient.id}>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center">
                        <div className="w-10 h-10 bg-gray-300 dark:bg-neutral-700 rounded-full flex items-center justify-center">
                          <span className="text-sm font-medium text-gray-700 dark:text-gray-200">{(patient.first_name?.[0] || '') + (patient.last_name?.[0] || '')}</span>
                        </div>
                        <div className="ml-4">
                          <div className="text-sm font-medium text-gray-900 dark:text-white">{patient.first_name} {patient.last_name}</div>
                          <div className="text-sm text-gray-500 dark:text-gray-400">ID: #PA{String(patient.id).padStart(5, '0')}</div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="text-sm text-gray-900 dark:text-white">{patient.phone}</div>
                      <div className="text-sm text-gray-500 dark:text-gray-400">{patient.email || 'No email'}</div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {patient.appointment_date
                        ? (
                            <>
                              <div className="text-sm text-gray-900 dark:text-white">
                                {new Date(`${patient.appointment_date}T${patient.appointment_time || '00:00'}`).toLocaleDateString('en-AU')}
                              </div>
                              <div className="text-sm text-gray-500 dark:text-gray-400">
                                {patient.appointment_time
                                  ? new Date(`1970-01-01T${patient.appointment_time}`).toLocaleTimeString('en-AU', { hour: '2-digit', minute: '2-digit', hour12: true })
                                  : '-'}
                              </div>
                            </>
                          )
                        : (
                            <>
                              <div className="text-sm text-gray-900 dark:text-white">-</div>
                              <div className="text-sm text-gray-500 dark:text-gray-400">-</div>
                            </>
                          )
                      }
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <StatusBadge status={patient.status || 'pending'} />
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                      <Button 
                        size="sm" 
                        className="bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md dark:bg-blue-500 dark:hover:bg-blue-400"
                      >
                        <svg 
                          xmlns="http://www.w3.org/2000/svg" 
                          width="24" 
                          height="24" 
                          viewBox="0 0 24 24" 
                          fill="none" 
                          stroke="currentColor" 
                          strokeWidth="2" 
                          strokeLinecap="round" 
                          strokeLinejoin="round" 
                          className="h-4 w-4 mr-1"
                        >
                          <path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"></path>
                          <path d="m21.854 2.147-10.94 10.939"></path>
                        </svg>
                        SMS Form
                      </Button>
                      <Button 
                        size="sm" 
                        variant="ghost"
                        className="inline-flex items-center justify-center gap-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800"
                        title="View"
                      >
                        <svg 
                          xmlns="http://www.w3.org/2000/svg" 
                          width="24" 
                          height="24" 
                          viewBox="0 0 24 24" 
                          fill="none" 
                          stroke="currentColor" 
                          strokeWidth="2" 
                          strokeLinecap="round" 
                          strokeLinejoin="round" 
                          className="h-4 w-4"
                        >
                          <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                          <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                      </Button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        )}
      </div>
      {/* Pagination */}
      {safeData && safeData.last_page > 1 && (
        <div className="px-6 py-4 border-t border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 flex items-center justify-between">
          <div className="text-sm text-gray-700 dark:text-gray-300">
            Showing <span className="font-medium">{(safeData.current_page - 1) * safeData.per_page + 1}</span> to <span className="font-medium">{Math.min(safeData.current_page * safeData.per_page, safeData.total)}</span> of <span className="font-medium">{safeData.total}</span> patients
          </div>
          <div className="flex items-center space-x-2">
            <Button size="sm" variant="outline" disabled={safeData.current_page === 1} onClick={() => setPage(page - 1)}>Previous</Button>
            <Button size="sm" className="bg-blue-600 text-white" disabled>{safeData.current_page}</Button>
            <Button size="sm" variant="outline" disabled={safeData.current_page === safeData.last_page} onClick={() => setPage(page + 1)}>Next</Button>
          </div>
        </div>
      )}
    </div>
  );
} 