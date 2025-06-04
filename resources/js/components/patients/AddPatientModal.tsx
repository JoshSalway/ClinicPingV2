import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger, DialogFooter, DialogClose, DialogDescription } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function AddPatientModal() {
  const [open, setOpen] = useState(false);
  const { data, setData, processing, errors, reset } = useForm({
    first_name: '',
    last_name: '',
    phone: '',
    email: '',
    appointment_date: '',
    appointment_time: '',
  });

  function handleClose() {
    setOpen(false);
    reset();
  }

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    // TODO: submit to backend
    setOpen(false);
    reset();
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button className="bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md dark:bg-blue-500 dark:hover:bg-blue-400 inline-flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor" className="h-4 w-4">
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Add Patient
        </Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Add Patient</DialogTitle>
          <DialogDescription>
            Enter patient details below
          </DialogDescription>
        </DialogHeader>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label htmlFor="first_name">First Name</Label>
              <Input
                id="first_name"
                value={data.first_name}
                onChange={e => setData('first_name', e.target.value)}
                required
                autoFocus
                placeholder="First name"
                disabled={processing}
              />
              {errors.first_name && <div className="text-destructive text-sm mt-1">{errors.first_name}</div>}
            </div>
            <div>
              <Label htmlFor="last_name">Last Name</Label>
              <Input
                id="last_name"
                value={data.last_name}
                onChange={e => setData('last_name', e.target.value)}
                required
                placeholder="Last name"
                disabled={processing}
              />
              {errors.last_name && <div className="text-destructive text-sm mt-1">{errors.last_name}</div>}
            </div>
          </div>
          <div>
            <Label htmlFor="phone">Phone</Label>
            <Input
              id="phone"
              value={data.phone}
              onChange={e => setData('phone', e.target.value)}
              required
              placeholder="+61 412 345 678"
              disabled={processing}
            />
            {errors.phone && <div className="text-destructive text-sm mt-1">{errors.phone}</div>}
          </div>
          <div>
            <Label htmlFor="email">Email</Label>
            <Input
              id="email"
              type="email"
              value={data.email}
              onChange={e => setData('email', e.target.value)}
              required
              placeholder="email@example.com"
              disabled={processing}
            />
            {errors.email && <div className="text-destructive text-sm mt-1">{errors.email}</div>}
          </div>
          <div className="flex gap-2">
            <div className="flex-1">
              <Label htmlFor="appointment_date">Appointment Date</Label>
              <Input
                id="appointment_date"
                type="date"
                value={data.appointment_date}
                onChange={e => setData('appointment_date', e.target.value)}
                required
                disabled={processing}
              />
              {errors.appointment_date && <div className="text-destructive text-sm mt-1">{errors.appointment_date}</div>}
            </div>
            <div className="flex-1">
              <Label htmlFor="appointment_time">Appointment Time</Label>
              <Input
                id="appointment_time"
                type="time"
                value={data.appointment_time}
                onChange={e => setData('appointment_time', e.target.value)}
                required
                disabled={processing}
              />
              {errors.appointment_time && <div className="text-destructive text-sm mt-1">{errors.appointment_time}</div>}
            </div>
          </div>
          <DialogFooter>
            <DialogClose asChild>
              <Button type="button" variant="outline" onClick={handleClose} disabled={processing}>Cancel</Button>
            </DialogClose>
            <Button type="submit" className="bg-blue-600 hover:bg-blue-700 text-white" disabled={processing}>
              {processing ? 'Adding...' : 'Add Patient'}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
} 