import { useEffect, useState } from 'react';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogClose } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import StatusBadge from './status-badge';

interface SmsMessage {
  id: number;
  content: string;
  status: string;
  sent_at: string;
}

interface PatientDetails {
  id: number;
  first_name: string;
  last_name: string;
  phone: string;
  email?: string;
  appointment_date?: string;
  appointment_time?: string;
  status: string;
  last_sent_at?: string;
  sms_messages: SmsMessage[];
}

interface PatientDetailsModalProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  patientId: number | null;
  onClose?: () => void;
}

const SMS_PER_PAGE = 5;

type StatusType = 'completed' | 'sent' | 'pending' | 'failed';

function toStatusType(status: string): StatusType {
  if (status === 'completed' || status === 'sent' || status === 'pending' || status === 'failed') return status as StatusType;
  return 'pending';
}

export default function PatientDetailsModal({ open, onOpenChange, patientId, onClose }: PatientDetailsModalProps) {
  const [loading, setLoading] = useState(false);
  const [patient, setPatient] = useState<PatientDetails | null>(null);
  const [page, setPage] = useState(1);

  useEffect(() => {
    if (open && patientId) {
      setLoading(true);
      fetch(`/api/patients/${patientId}`)
        .then(res => res.json())
        .then(data => { setPatient(data); setPage(1); })
        .finally(() => setLoading(false));
    } else {
      setPatient(null);
      setPage(1);
    }
  }, [open, patientId]);

  const smsMessages = patient?.sms_messages || [];
  // Derive status and timestamps from latest SMS
  let status: StatusType = 'pending';
  let lastSentAt: string | undefined = undefined;
  let completedAt: string | undefined = undefined;
  if (smsMessages.length > 0) {
    const latestSms = [...smsMessages]
      .sort((a, b) => {
        const aTime = a.sent_at ? new Date(a.sent_at).getTime() : 0;
        const bTime = b.sent_at ? new Date(b.sent_at).getTime() : 0;
        return bTime - aTime;
      })[0];
    if (latestSms.sent_at) {
      lastSentAt = latestSms.sent_at;
      if ((latestSms as any).completed_at) {
        completedAt = (latestSms as any).completed_at;
        status = 'completed';
      } else {
        status = 'sent';
      }
    } else {
      status = 'pending';
    }
  }
  // Sort messages by completed_at (most recent completed at top), then sent_at
  const sortedSms = [...smsMessages].sort((a, b) => {
    const aTime = (a as any).completed_at ? new Date((a as any).completed_at).getTime() : (a.sent_at ? new Date(a.sent_at).getTime() : 0);
    const bTime = (b as any).completed_at ? new Date((b as any).completed_at).getTime() : (b.sent_at ? new Date(b.sent_at).getTime() : 0);
    return bTime - aTime;
  });
  const totalPages = Math.ceil(sortedSms.length / SMS_PER_PAGE);
  const paginatedSms = sortedSms.slice((page - 1) * SMS_PER_PAGE, page * SMS_PER_PAGE);

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-lg" aria-describedby="patient-details-description">
        <DialogHeader>
          <DialogTitle>Patient Details</DialogTitle>
        </DialogHeader>
        {loading ? (
          <div className="p-8 text-center text-gray-500">Loading...</div>
        ) : patient ? (
          <div className="space-y-6">
            {/* Patient Info Card */}
            <div className="rounded-lg border bg-white dark:bg-neutral-900 p-4 shadow-sm">
              <div className="font-semibold text-lg mb-1">{patient.first_name} {patient.last_name}</div>
              <div className="text-gray-500 text-sm mb-1">ID: #PA{String(patient.id).padStart(5, '0')}</div>
              <div className="text-gray-500 text-sm mb-1">{patient.phone}</div>
              <div className="text-gray-500 text-sm mb-1">{patient.email}</div>
              <div className="text-gray-500 text-sm mb-1">
                Appointment: {patient.appointment_date && patient.appointment_time
                  ? new Date(`${patient.appointment_date}T${patient.appointment_time}`).toLocaleString('en-AU', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true })
                  : '-'}
              </div>
              <div className="flex items-center gap-2 text-gray-500 text-sm mb-1">
                Status: <StatusBadge status={status} />
              </div>
              <div className="text-gray-500 text-sm">Last Sent: {lastSentAt ? new Date(lastSentAt).toLocaleString('en-AU', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }) : 'Never'}</div>
              {completedAt && (
                <div className="text-gray-500 text-sm">Completed: {new Date(completedAt).toLocaleString('en-AU', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true })}</div>
              )}
            </div>
            {/* Form History Card */}
            <div className="rounded-lg border bg-white dark:bg-neutral-900 p-4 shadow-sm">
              <div className="font-semibold mb-2">Form History</div>
              {smsMessages.length === 0 ? (
                <div className="text-gray-400 text-sm">No form history available.</div>
              ) : (
                <>
                  <ul className="space-y-3 mb-4">
                    {paginatedSms.filter(sms => sms.status === 'sent' || sms.status === 'completed').map(sms => (
                      <li key={sms.id} className="border rounded p-3 bg-gray-50 dark:bg-neutral-800">
                        <div className="flex items-center justify-between mb-1">
                          <span className="text-xs text-gray-500">{new Date(sms.sent_at).toLocaleString('en-AU', { day: '2-digit', month: 'short', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true })}</span>
                          <StatusBadge status={['completed', 'sent', 'pending', 'failed'].includes(sms.status) ? (sms.status as StatusType) : 'pending'} />
                        </div>
                        {sms.status === 'sent' && (
                          <>
                            <div className="text-xs text-gray-500 mb-1">Sent to: {patient.phone}</div>
                            <div className="text-sm text-gray-900 dark:text-white">{sms.content}</div>
                          </>
                        )}
                        {sms.status === 'completed' && (
                          <div className="text-sm text-green-700 dark:text-green-300 font-semibold">Form completed</div>
                        )}
                      </li>
                    ))}
                  </ul>
                  {totalPages > 1 && (
                    <div className="flex justify-between items-center">
                      <Button size="sm" variant="outline" disabled={page === 1} onClick={() => setPage(page - 1)}>Previous</Button>
                      <span className="text-xs text-gray-500">Page {page} of {totalPages}</span>
                      <Button size="sm" variant="outline" disabled={page === totalPages} onClick={() => setPage(page + 1)}>Next</Button>
                    </div>
                  )}
                </>
              )}
            </div>
          </div>
        ) : null}
        <DialogFooter>
          <DialogClose asChild>
            <Button variant="outline">Close</Button>
          </DialogClose>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
} 