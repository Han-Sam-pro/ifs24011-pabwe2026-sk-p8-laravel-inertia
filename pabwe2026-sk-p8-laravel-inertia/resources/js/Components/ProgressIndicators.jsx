import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { Spinner } from './Icons';

/** Bilah progres tipis di bagian atas saat navigasi/request Inertia berjalan. */
export function TopProgressBar() {
    const [visible, setVisible] = useState(false);
    const [width, setWidth] = useState(0);

    useEffect(() => {
        let interval = null;
        let hideTimer = null;

        const offStart = router.on('start', () => {
            clearTimeout(hideTimer);
            setVisible(true);
            setWidth(12);
            interval = setInterval(() => setWidth((w) => (w < 90 ? w + (90 - w) * 0.08 : w)), 200);
        });

        const offFinish = router.on('finish', () => {
            clearInterval(interval);
            setWidth(100);
            hideTimer = setTimeout(() => {
                setVisible(false);
                setWidth(0);
            }, 250);
        });

        return () => {
            offStart();
            offFinish();
            clearInterval(interval);
            clearTimeout(hideTimer);
        };
    }, []);

    if (!visible) return null;

    return (
        <div className="fixed inset-x-0 top-0 z-[80] h-1" aria-hidden="true">
            <div className="h-full bg-indigo-500 transition-all duration-200 ease-out" style={{ width: `${width}%` }} />
        </div>
    );
}

/** Status "Memproses tindakan..." mengambang untuk request non-GET (simpan, hapus, AI, dst.). */
export function FloatingStatusPill() {
    const [pending, setPending] = useState(0);

    useEffect(() => {
        const offStart = router.on('start', (event) => {
            if (event.detail?.visit?.method !== 'get') setPending((n) => n + 1);
        });
        const offFinish = router.on('finish', (event) => {
            if (event.detail?.visit?.method !== 'get') setPending((n) => Math.max(0, n - 1));
        });

        return () => {
            offStart();
            offFinish();
        };
    }, []);

    if (pending === 0) return null;

    return (
        <div
            role="status"
            aria-live="polite"
            className="fixed bottom-4 left-1/2 z-[70] flex -translate-x-1/2 items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-lg"
        >
            <Spinner />
            Memproses tindakan...
        </div>
    );
}
