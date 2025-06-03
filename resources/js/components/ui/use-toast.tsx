import * as React from 'react';

interface Toast {
  id: string;
  title?: string;
  description?: string;
  type?: 'success' | 'error' | 'info';
}

const ToastContext = React.createContext<{
  toasts: Toast[];
  addToast: (toast: Toast) => void;
  removeToast: (id: string) => void;
} | null>(null);

export function ToastProvider({ children }: { children: React.ReactNode }) {
  const [toasts, setToasts] = React.useState<Toast[]>([]);

  const addToast = (toast: Toast) => {
    setToasts((prev) => [...prev, { ...toast, id: toast.id || Math.random().toString(36) }]);
    setTimeout(() => removeToast(toast.id), 4000);
  };
  const removeToast = (id: string) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
  };

  return (
    <ToastContext.Provider value={{ toasts, addToast, removeToast }}>
      {children}
      <div className="fixed top-4 right-4 z-50 space-y-2">
        {toasts.map((toast) => (
          <div key={toast.id} className={`rounded shadow px-4 py-2 text-white ${toast.type === 'error' ? 'bg-red-600' : toast.type === 'success' ? 'bg-green-600' : 'bg-gray-800'}`}>
            <div className="font-semibold">{toast.title}</div>
            {toast.description && <div className="text-sm">{toast.description}</div>}
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  );
}

export function useToast() {
  const ctx = React.useContext(ToastContext);
  if (!ctx) throw new Error('useToast must be used within a ToastProvider');
  return ctx;
} 