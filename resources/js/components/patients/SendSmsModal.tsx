import { useState } from 'react';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogClose } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { useToast } from '@/components/ui/use-toast';

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
}

export default function SendSmsModal({ open, onOpenChange, patient, onSent }: SendSmsModalProps) {
  const [loading, setLoading] = useState(false);
  const { addToast } = useToast();
  const message = 'Please complete your medical history form: [form link]';

  async function handleSend() {
    if (!patient) return;
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
        <DialogFooter>
          <DialogClose asChild>
            <Button variant="outline" disabled={loading}>Cancel</Button>
          </DialogClose>
          <Button onClick={handleSend} disabled={loading || !patient}>
            {loading ? 'Sending...' : 'Confirm & Send'}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
} 