import { Link, router, usePage } from '@inertiajs/react';
import Flash from '../Components/Flash';
import { FloatingStatusPill, TopProgressBar } from '../Components/ProgressIndicators';
import { initials } from '../lib/format';

const memberNav = [
    { href: '/dashboard/posts', label: 'Artikel Saya' },
    { href: '/profile', label: 'Profil' },
];

const adminNav = [
    { href: '/admin/users', label: 'Pengguna' },
    { href: '/admin/posts', label: 'Semua Artikel' },
    { href: '/profile', label: 'Profil' },
];

export default function AppLayout({ title, children }) {
    const { auth } = usePage().props;
    const user = auth.user;
    const items = user.role === 'admin' ? adminNav : memberNav;

    return (
        <div className="min-h-screen bg-slate-50">
            <TopProgressBar />
            <FloatingStatusPill />
            <Flash />

            <div className="flex min-h-screen">
                <aside className="hidden w-60 shrink-0 flex-col bg-slate-900 px-4 py-6 text-slate-200 md:flex">
                    <Link href="/" className="mb-8 cursor-pointer px-2 text-lg font-bold text-white">
                        Blog Pabwe
                    </Link>
                    <nav className="flex flex-col gap-1 text-sm font-medium">
                        {items.map((item) => (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={`cursor-pointer rounded-lg px-3 py-2 hover:bg-white/10 ${
                                    window.location.pathname.startsWith(item.href) ? 'bg-white/10 text-white' : ''
                                }`}
                            >
                                {item.label}
                            </Link>
                        ))}
                    </nav>
                    <div className="mt-auto space-y-3 border-t border-white/10 pt-4 text-sm">
                        <div className="flex items-center gap-3">
                            <span className="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-500 text-xs font-bold text-white">
                                {initials(user.name)}
                            </span>
                            <div className="min-w-0">
                                <p className="truncate font-semibold text-white">{user.name}</p>
                                <p className="text-xs text-slate-400">{user.role === 'admin' ? 'Admin' : 'Anggota'}</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            onClick={() => router.post('/logout')}
                            className="w-full cursor-pointer rounded-lg px-3 py-2 text-left hover:bg-white/10"
                        >
                            Keluar
                        </button>
                    </div>
                </aside>

                <div className="flex min-w-0 flex-1 flex-col">
                    <header className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-4 sm:px-6">
                        <div className="flex items-center gap-3">
                            <Link href="/" className="cursor-pointer text-sm text-slate-500 hover:text-indigo-700 md:hidden">
                                Beranda
                            </Link>
                            <h1 className="text-xl font-bold text-slate-900">{title}</h1>
                        </div>
                        <nav className="flex gap-2 text-sm font-medium md:hidden">
                            {items.map((item) => (
                                <Link key={item.href} href={item.href} className="cursor-pointer rounded-lg px-2 py-1 text-indigo-700 hover:bg-indigo-50">
                                    {item.label}
                                </Link>
                            ))}
                        </nav>
                    </header>

                    <main className="flex-1 px-4 py-6 sm:px-6">{children}</main>
                </div>
            </div>
        </div>
    );
}
