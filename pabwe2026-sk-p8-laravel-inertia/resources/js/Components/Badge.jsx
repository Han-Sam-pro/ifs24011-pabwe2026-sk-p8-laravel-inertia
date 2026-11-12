export function StatusBadge({ published }) {
    return (
        <span
            className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${
                published ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'
            }`}
        >
            {published ? 'Publik' : 'Draf'}
        </span>
    );
}

export function CategoryBadge({ children }) {
    return (
        <span className="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700">
            {children}
        </span>
    );
}
