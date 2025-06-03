import { useState, useEffect, useRef } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import AddPatientModal from '@/components/patients/AddPatientModal';
import StatusBadge from './status-badge';
import SendSmsModal from './SendSmsModal';
import PatientDetailsModal from './PatientDetailsModal';

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
  last_sent_at?: string;
}

interface PatientApiResponse {
  data: Patient[];
  current_page: number;
  last_page: number;
  total: number;
}

export default function PatientTable() {
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('all');
  const [sortBy, setSortBy] = useState('appointment_at');
  const [sortDir, setSortDir] = useState<'asc' | 'desc'>('asc');
  const [page, setPage] = useState(1);
  const [showTodayOnly, setShowTodayOnly] = useState(true);
  const [smsModalOpen, setSmsModalOpen] = useState(false);
  const [detailsModalOpen, setDetailsModalOpen] = useState(false);
  const [selectedPatient, setSelectedPatient] = useState<Patient | null>(null);
  const [selectedPatientId, setSelectedPatientId] = useState<number | null>(null);
  const [cooldowns, setCooldowns] = useState<{ [id: number]: number }>({});
  const intervalRef = useRef<NodeJS.Timeout | null>(null);

  const { data, isLoading, isError } = useQuery<PatientApiResponse>({
    queryKey: ['patients', { search, status, sortBy, sortDir, page, showTodayOnly }],
    queryFn: async () => {
      const params = new URLSearchParams({ 
        search, 
        status, 
        sortBy, 
        sortDir,
        page: String(page),
        today_only: String(showTodayOnly)
      });
      const res = await fetch(`/api/patients?${params}`);
      if (!res.ok) throw new Error('Failed to fetch');
      return res.json();
    },
  });

  const safeData: PatientApiResponse = data || { data: [], current_page: 1, last_page: 1, total: 0 };

  // Countdown effect
  useEffect(() => {
    if (Object.keys(cooldowns).length === 0) return;
    intervalRef.current = setInterval(() => {
      setCooldowns(prev => {
        const now = Date.now();
        const updated: typeof prev = {};
        Object.entries(prev).forEach(([id, until]) => {
          if (until > now) updated[Number(id)] = until;
        });
        return updated;
      });
    }, 1000);
    return () => { if (intervalRef.current) clearInterval(intervalRef.current); };
  }, [cooldowns]);

  function handleClearFilters() {
    setSearch('');
    setStatus('all');
    setSortBy('appointment_at');
    setSortDir('asc');
    setShowTodayOnly(true);
    setPage(1);
  }

  function handleSortChange(value: string) {
    setSortBy(value);
    if (value === 'appointment_at') {
      setSortDir('asc');
    } else {
      setSortDir('asc'); // or 'desc' for last_sent, adjust as needed
    }
    setPage(1);
  }

  function handleTodayToggle() {
    setShowTodayOnly(v => {
      const newVal = !v;
      setSortBy('appointment_at');
      setSortDir('asc');
      setPage(1);
      return newVal;
    });
  }

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
          className="md:w-80 h-12 bg-white text-gray-900 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-neutral-800 dark:text-white dark:border-neutral-700 dark:placeholder:text-neutral-400"
        />
        <div className="flex gap-4 items-center">
          <Button
            variant={showTodayOnly ? "default" : "outline"}
            onClick={handleTodayToggle}
            className={`h-12 px-6 text-base font-semibold flex items-center ${showTodayOnly ? 'bg-purple-600 hover:bg-purple-700 text-white shadow-md' : 'bg-white text-purple-700 border border-purple-200 dark:bg-neutral-800 dark:text-purple-300 dark:border-neutral-700'} rounded-lg`}
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={`h-6 w-6 mr-2 ${showTodayOnly ? 'text-white' : 'text-purple-600 dark:text-purple-300'}`}> <path d="M8 2v4"></path><path d="M16 2v4"></path><rect width="18" height="18" x="3" y="4" rx="2"></rect><path d="M3 10h18"></path></svg>
            Today's Appointments
          </Button>
          <Select value={status} onValueChange={v => { setStatus(v); setPage(1); }}>
            <SelectTrigger className="w-40 h-12 bg-white text-gray-900 border border-gray-300 rounded-lg shadow-sm dark:bg-neutral-800 dark:text-white dark:border-neutral-700">
              <SelectValue placeholder="All Status" />
            </SelectTrigger>
            <SelectContent className="dark:bg-neutral-800 dark:text-white">
              <SelectItem value="all">All Status</SelectItem>
              <SelectItem value="sent">Form Sent</SelectItem>
              <SelectItem value="pending">Pending</SelectItem>
              <SelectItem value="completed">Completed</SelectItem>
              <SelectItem value="failed">Failed</SelectItem>
            </SelectContent>
          </Select>
          <Select value={sortBy} onValueChange={handleSortChange}>
            <SelectTrigger className="w-48 h-12 bg-white text-gray-900 border border-gray-300 rounded-lg shadow-sm dark:bg-neutral-800 dark:text-white dark:border-neutral-700">
              <SelectValue />
            </SelectTrigger>
            <SelectContent className="dark:bg-neutral-800 dark:text-white">
              <SelectItem value="name">Sort by Name</SelectItem>
              <SelectItem value="appointment_at">Sort by Appointment</SelectItem>
              <SelectItem value="last_sent">Sort by Last Sent</SelectItem>
            </SelectContent>
          </Select>
          {(search || status !== 'all' || !showTodayOnly || sortBy !== 'appointment_at') && (
            <Button variant="outline" onClick={handleClearFilters} className="h-12 px-5 text-base font-semibold flex items-center gap-2 border border-gray-300 bg-white text-gray-700 rounded-lg dark:bg-neutral-800 dark:text-white dark:border-neutral-700 dark:hover:bg-neutral-700">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2" className="mr-1"><path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
              Clear Filters
            </Button>
          )}
        </div>
      </div>
      {/* Table */}
      <div className="overflow-x-auto">
        {isLoading ? (
          <div className="p-8 text-center text-gray-500">Loading...</div>
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
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Sent</th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody className="bg-white dark:bg-neutral-900 divide-y divide-gray-200 dark:divide-neutral-800">
              {safeData.data.length === 0 ? (
                <tr>
                  <td colSpan={6} className="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No patients found.</td>
                </tr>
              ) : (
                safeData.data.map((patient: Patient) => {
                  const cooldownUntil = cooldowns[patient.id] || 0;
                  const now = Date.now();
                  const secondsLeft = Math.max(0, Math.ceil((cooldownUntil - now) / 1000));
                  return (
                    <tr key={patient.id}>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="flex items-center">
                          <div className="w-10 h-10 bg-gray-300 dark:bg-neutral-700 rounded-full flex items-center justify-center">
                            <span className="text-sm font-medium text-gray-700 dark:text-gray-200">
                              {patient.first_name?.[0]}{patient.last_name?.[0]}
                            </span>
                          </div>
                          <div className="ml-4">
                            <div className="text-sm font-medium text-gray-900 dark:text-white">
                              {patient.first_name} {patient.last_name}
                            </div>
                            <div className="text-sm text-gray-500 dark:text-gray-400">
                              ID: #PA{String(patient.id).padStart(5, '0')}
                            </div>
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
                              (() => {
                                // Combine date and time into a UTC date
                                const utcDate = new Date(`${patient.appointment_date}T${patient.appointment_time || '00:00'}Z`);
                                return (
                                  <>
                                    <div className="text-sm text-gray-900 dark:text-white">
                                      {utcDate.toLocaleDateString('en-AU', { timeZone: 'Australia/Sydney' })}
                                    </div>
                                    <div className="text-sm text-gray-500 dark:text-gray-400">
                                      {utcDate.toLocaleTimeString('en-AU', { hour: '2-digit', minute: '2-digit', hour12: true, timeZone: 'Australia/Sydney' })}
                                    </div>
                                  </>
                                );
                              })()
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
                      <td className="px-6 py-4 whitespace-nowrap">
                        {patient.status !== 'pending' && (
                          <div className="text-sm text-gray-900 dark:text-white">
                            {patient.last_sent_at
                              ? new Date(patient.last_sent_at).toLocaleString('en-AU', {
                                  dateStyle: 'short',
                                  timeStyle: 'short',
                                  timeZone: 'Australia/Sydney',
                                })
                              : 'Never'}
                          </div>
                        )}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                        <Button
                          size="sm"
                          className="bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md dark:bg-blue-500 dark:hover:bg-blue-400"
                          onClick={() => {
                            setSelectedPatient(patient);
                            setSmsModalOpen(true);
                          }}
                          disabled={secondsLeft > 0}
                        >
                          {secondsLeft > 0 ? (
                            <span>{secondsLeft}s</span>
                          ) : (
                            <>
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
                            </>
                          )}
                        </Button>
                        <Button 
                          size="sm" 
                          variant="ghost"
                          className="inline-flex items-center justify-center gap-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800"
                          title="View"
                          onClick={() => { setSelectedPatientId(patient.id); setDetailsModalOpen(true); }}
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
                  );
                })
              )}
            </tbody>
          </table>
        )}
      </div>
      {/* Pagination */}
      <div className="px-6 py-4 border-t border-gray-200 dark:border-neutral-800">
        <div className="flex items-center justify-between">
          <div className="text-sm text-gray-700 dark:text-gray-300">
            Showing <span className="font-medium">{safeData.data.length}</span> of{' '}
            <span className="font-medium">{safeData.total}</span> patients
          </div>
          <div className="flex items-center space-x-2">
            <Button
              variant="outline"
              size="sm"
              onClick={() => setPage(p => Math.max(1, p - 1))}
              disabled={page === 1}
            >
              Previous
            </Button>
            <Button
              variant="outline"
              size="sm"
              onClick={() => setPage(p => Math.min(safeData.last_page, p + 1))}
              disabled={page === safeData.last_page}
            >
              Next
            </Button>
          </div>
        </div>
      </div>
      {/* Modals */}
      {selectedPatient && (
        <SendSmsModal
          open={smsModalOpen}
          onOpenChange={setSmsModalOpen}
          patient={selectedPatient}
          onSent={() => {
            setSmsModalOpen(false);
            setCooldowns(prev => ({ ...prev, [selectedPatient.id]: Date.now() + 60000 }));
            setSelectedPatient(null);
          }}
        />
      )}
      {selectedPatientId && (
        <PatientDetailsModal
          open={detailsModalOpen}
          onOpenChange={setDetailsModalOpen}
          patientId={selectedPatientId}
        />
      )}
    </div>
  );
} 