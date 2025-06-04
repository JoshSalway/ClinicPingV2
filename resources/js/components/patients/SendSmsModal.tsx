import { useState, useEffect } from 'react';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogClose } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { useToast } from '@/components/ui/use-toast';

// Add prop for smsMessages
interface SmsMessage {
  id: number;
  status: string;
  sent_at: string;
}

interface SendSmsModalProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  patient: {
    id: number;
    first_name: string;
    last_name: string;
    phone: string;
  } | null;
  onSent: () => void;
  smsMessages?: SmsMessage[]; // optional, for warning
}

export default function SendSmsModal({ open, onOpenChange, patient, onSent, smsMessages = [] }: SendSmsModalProps) {
  const [loading, setLoading] = useState(false);
  const { addToast } = useToast();
  const message = 'Please complete your medical history form: [form link]';
  const [warnConfirmed, setWarnConfirmed] = useState(false);

  // Check if a form was sent or completed today
  const today = new Date().toISOString().slice(0, 10);
  const sentOrCompletedToday = smsMessages.some(sms =>
    (sms.status === 'sent' || sms.status === 'completed') &&
    sms.sent_at && sms.sent_at.slice(0, 10) === today
  );

  async function handleSend() {
    if (!patient) return;
    // If warning needed and not confirmed, show warning
    if (sentOrCompletedToday && !warnConfirmed) {
      return;
    }
    setLoading(true);
    try {
      const res = await fetch('/api/sms/send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ patient_id: patient.id, message }),
      });
      if (!res.ok) {
        const data = await res.json();
        throw new Error(data.error || 'Failed to send SMS');
      }
      addToast({ id: Math.random().toString(36), type: 'success', title: 'Message sent!' });
      onSent();
      onOpenChange(false);
    } catch (e: any) {
      addToast({ id: Math.random().toString(36), type: 'error', title: 'Failed to send SMS', description: e.message });
    } finally {
      setLoading(false);
      setWarnConfirmed(false);
    }
  }

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Send SMS Form</DialogTitle>
        </DialogHeader>
        {patient && (
          <div className="mb-4 bg-gray-50 rounded-lg p-4 dark:bg-neutral-800">
            <div className="mb-2">
              <div className="text-xs text-gray-500 dark:text-gray-400">Recipient</div>
              <div className="font-semibold text-lg dark:text-white">{patient.first_name} {patient.last_name}</div>
              <div className="text-gray-500 text-sm dark:text-gray-400">{patient.phone}</div>
            </div>
            <div>
              <div className="text-xs text-gray-500 mb-1 dark:text-gray-400">Message Preview</div>
              <div className="bg-white border rounded p-2 text-sm dark:bg-neutral-900 dark:text-white dark:border-neutral-700">{message}</div>
            </div>
          </div>
        )}
        {/* Warning Dialog */}
        {sentOrCompletedToday && !warnConfirmed && (
          <div className="mb-4 p-3 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 rounded">
            <div className="font-semibold mb-1">Warning</div>
            <div>A form was already sent or completed for this patient today. Are you sure you want to send another form?</div>
            <div className="flex gap-2 mt-3">
              <Button variant="outline" onClick={() => { onOpenChange(false); }} disabled={loading}>Cancel</Button>
              <Button onClick={() => { setWarnConfirmed(true); }} disabled={loading} className="bg-yellow-600 text-white hover:bg-yellow-700">Send Anyway</Button>
            </div>
          </div>
        )}
        <DialogFooter>
          <DialogClose asChild>
            <Button variant="outline" disabled={loading}>Cancel</Button>
          </DialogClose>
          <Button onClick={handleSend} disabled={loading || !patient || (sentOrCompletedToday && !warnConfirmed)}>
            {loading ? 'Sending...' : 'Confirm & Send'}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
} 