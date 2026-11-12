import { useEffect, useRef, useState } from 'react';
import { DotsIcon } from './Icons';

/**
 * Menu kebab (⋮). items: [{ label, icon, onSelect | href, tone: 'primary' | 'danger' }]
 */
export default function KebabMenu({ items }) {
    const [open, setOpen] = useState(false);
    const ref = useRef(null);

    useEffect(() => {
        if (!open) return undefined;

        const onClick = (event) => {
            if (ref.current && !ref.current.contains(event.target)) setOpen(false);
        };
        document.addEventListener('mousedown', onClick);

        return () => document.removeEventListener('mousedown', onClick);
    }, [open]);

    return (
        <div className="relative" ref={ref}>
            <button
                type="button"
                onClick={() => setOpen((v) => !v)}
                aria-haspopup="menu"
                aria-expanded={open}
                aria-label="Menu aksi"
                className="cursor-pointer rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800"
            >
                <DotsIcon />
            </button>

            {open && (
                <ul
                    role="menu"
                    className="absolute right-0 z-20 mt-1 w-44 overflow-hidden rounded-xl bg-white py-1 shadow-xl ring-1 ring-slate-200"
                >
                    {items.map(({ label, icon: Icon, onSelect, tone = 'primary' }) => (
                        <li key={label} role="none">
                            <button
                                type="button"
                                role="menuitem"
                                onClick={() => {
                                    setOpen(false);
                                    onSelect();
                                }}
                                className={`flex w-full cursor-pointer items-center gap-2 px-3 py-2 text-left text-sm font-medium hover:bg-slate-50 ${
                                    tone === 'danger' ? 'text-red-600' : 'text-indigo-600'
                                }`}
                            >
                                <Icon className="h-4 w-4" />
                                {label}
                            </button>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
