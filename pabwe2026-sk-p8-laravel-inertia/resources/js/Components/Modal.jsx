import { useEffect } from 'react';
import { XIcon } from './Icons';

/**
 * Modal layar penuh: header & footer gelap (kontras), tombol aksi di kanan footer.
 */
export default function Modal({ open, title, onClose, footer = null, children }) {
    useEffect(() => {
        if (!open) return undefined;

        const onKey = (event) => event.key === 'Escape' && onClose?.();
        document.addEventListener('keydown', onKey);
        document.body.style.overflow = 'hidden';

        return () => {
            document.removeEventListener('keydown', onKey);
            document.body.style.overflow = '';
        };
    }, [open, onClose]);

    if (!open) return null;

    return (
        <div className="fixed inset-0 z-50 flex flex-col bg-white" role="dialog" aria-modal="true" aria-label={title}>
            <header className="flex items-center justify-between bg-slate-900 px-4 py-4 text-white sm:px-6">
                <h2 className="text-lg font-semibold">{title}</h2>
                <button
                    type="button"
                    onClick={onClose}
                    aria-label="Tutup"
                    className="cursor-pointer rounded-md p-1.5 text-white hover:bg-white/10"
                >
                    <XIcon />
                </button>
            </header>

            <div className="flex-1 overflow-y-auto px-4 py-6 sm:px-6">{children}</div>

            {footer && (
                <footer className="flex flex-wrap items-center justify-end gap-3 bg-slate-900 px-4 py-4 sm:px-6">
                    {footer}
                </footer>
            )}
        </div>
    );
}
