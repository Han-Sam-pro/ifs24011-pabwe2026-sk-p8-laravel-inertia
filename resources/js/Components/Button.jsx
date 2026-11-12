import { Spinner } from './Icons';

const variants = {
    primary: 'bg-indigo-600 text-white hover:bg-indigo-700 focus-visible:ring-indigo-500',
    secondary: 'bg-white text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50 focus-visible:ring-indigo-500',
    danger: 'bg-red-600 text-white hover:bg-red-700 focus-visible:ring-red-500',
    light: 'bg-white text-slate-900 hover:bg-slate-200 focus-visible:ring-white',
    ghost: 'text-slate-600 hover:bg-slate-100 focus-visible:ring-indigo-500',
};

export default function Button({
    variant = 'primary',
    loading = false,
    className = '',
    children,
    type = 'button',
    disabled,
    ...props
}) {
    return (
        <button
            type={type}
            disabled={disabled || loading}
            className={`inline-flex cursor-pointer items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 ${variants[variant]} ${className}`}
            {...props}
        >
            {loading && <Spinner />}
            {children}
        </button>
    );
}
