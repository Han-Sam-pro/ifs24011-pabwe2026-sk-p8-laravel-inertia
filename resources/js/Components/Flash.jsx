import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { CheckIcon, XIcon } from './Icons';

export default function Flash() {
    const { flash } = usePage().props;
    const [message, setMessage] = useState(null);

    useEffect(() => {
        const text = flash?.success || flash?.error;
        if (!text) return undefined;

        setMessage({ text, type: flash.error ? 'error' : 'success' });
        const timer = setTimeout(() => setMessage(null), 4500);

        return () => clearTimeout(timer);
    }, [flash?.success, flash?.error]);

    if (!message) return null;

    const isError = message.type === 'error';

    return (
        <div
            role="status"
            className={`fixed left-1/2 top-4 z-[70] flex w-[calc(100%-2rem)] max-w-md -translate-x-1/2 items-start gap-3 rounded-xl px-4 py-3 text-sm font-medium shadow-lg ${
                isError ? 'bg-red-600 text-white' : 'bg-emerald-600 text-white'
            }`}
        >
            {isError ? <XIcon className="mt-0.5 h-4 w-4 shrink-0" /> : <CheckIcon className="mt-0.5 h-4 w-4 shrink-0" />}
            <span className="flex-1">{message.text}</span>
            <button
                type="button"
                onClick={() => setMessage(null)}
                aria-label="Tutup notifikasi"
                className="cursor-pointer rounded p-0.5 hover:bg-white/20"
            >
                <XIcon className="h-4 w-4" />
            </button>
        </div>
    );
}
