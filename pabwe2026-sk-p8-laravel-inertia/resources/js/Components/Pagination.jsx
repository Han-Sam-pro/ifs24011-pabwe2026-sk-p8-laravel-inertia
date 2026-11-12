import { Link } from '@inertiajs/react';

/** Navigasi halaman dari objek paginator Laravel ({ links: [...] }). */
export default function Pagination({ paginator }) {
    if (!paginator || paginator.last_page <= 1) return null;

    const links = paginator.links;
    const last = links.length - 1;

    return (
        <nav className="mt-6 flex flex-wrap items-center justify-center gap-1" aria-label="Pagination">
            {links.map((link, index) => {
                const label = index === 0 ? 'Sebelumnya' : index === last ? 'Berikutnya' : link.label;
                const base = 'rounded-lg px-3 py-1.5 text-sm font-medium cursor-pointer';

                if (!link.url) {
                    return (
                        <span key={index} className={`${base} text-slate-400`}>
                            {label}
                        </span>
                    );
                }

                return (
                    <Link
                        key={index}
                        href={link.url}
                        preserveScroll
                        className={`${base} ${link.active ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-200'}`}
                    >
                        {label}
                    </Link>
                );
            })}
        </nav>
    );
}
