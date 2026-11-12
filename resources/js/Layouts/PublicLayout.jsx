import { Link, router, usePage } from '@inertiajs/react';
import Flash from '../Components/Flash';
import { FloatingStatusPill, TopProgressBar } from '../Components/ProgressIndicators';
import { initials } from '../lib/format';

export default function PublicLayout({ children }) {
    const { auth } = usePage().props;
    const user = auth?.user;

    return (
        <div className="flex min-h-screen flex-col">
            <TopProgressBar />
            <FloatingStatusPill />
            <Flash />

            <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
                <div className="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3">
                    <Link href="/" className="cursor-pointer text-lg font-bold text-indigo-700">
                        Blog Pabwe
                    </Link>

                    <nav className="flex items-center gap-2 text-sm font-medium">
                        <Link href="/articles" className="cursor-pointer rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100">
                            Artikel
                        </Link>

                        {!user && (
                            <>
                                <Link href="/login" className="cursor-pointer rounded-lg px-3 py-2 text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50">
                                    Masuk
                                </Link>
                                <Link href="/register" className="cursor-pointer rounded-lg bg-indigo-600 px-3 py-2 text-white hover:bg-indigo-700">
                                    Daftar
                                </Link>
                            </>
                        )}

                        {user && (
                            <div className="flex items-center gap-2">
                                <Link
                                    href={user.role === 'admin' ? '/admin/users' : '/dashboard/posts'}
                                    className="cursor-pointer rounded-lg px-3 py-2 text-indigo-700 hover:bg-indigo-50"
                                >
                                    {user.role === 'admin' ? 'Panel Admin' : 'Dashboard Penulis'}
                                </Link>
                                <span
                                    title={user.name}
                                    className="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white"
                                >
                                    {initials(user.name)}
                                </span>
                                <button
                                    type="button"
                                    onClick={() => router.post('/logout')}
                                    className="cursor-pointer rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100"
                                >
                                    Keluar
                                </button>
                            </div>
                        )}
                    </nav>
                </div>
            </header>

            <main className="flex-1">{children}</main>

            <footer className="border-t border-slate-200 bg-white">
                <div className="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-8 text-sm text-slate-600">
                    <p>© {new Date().getFullYear()} Blog Pabwe · Tulisan untuk belajar dan berbagi.</p>
                    <nav className="flex gap-4">
                        <Link href="/" className="cursor-pointer hover:text-indigo-700">Beranda</Link>
                        {!user && <Link href="/login" className="cursor-pointer hover:text-indigo-700">Masuk</Link>}
                        {!user && <Link href="/register" className="cursor-pointer hover:text-indigo-700">Daftar</Link>}
                    </nav>
                </div>
            </footer>
        </div>
    );
}
