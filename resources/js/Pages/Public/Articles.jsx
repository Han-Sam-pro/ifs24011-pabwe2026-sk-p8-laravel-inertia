import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import ArticleCard from '../../Components/ArticleCard';
import Pagination from '../../Components/Pagination';
import PublicLayout from '../../Layouts/PublicLayout';

export default function Articles({ posts, filters, categories }) {
    const [q, setQ] = useState(filters.q ?? '');
    const [kategori, setKategori] = useState(filters.kategori ?? '');

    const submit = (event) => {
        event.preventDefault();
        router.get('/articles', { q, kategori }, { preserveState: true, preserveScroll: true, replace: true });
    };

    return (
        <PublicLayout>
            <Head title="Artikel"><meta name="description" content="Daftar artikel Blog Pabwe. Cari dan saring berdasarkan kategori." /></Head>
            <div className="mx-auto max-w-6xl px-4 py-10">
                <h1 className="text-3xl font-bold text-slate-900">Semua artikel</h1>

                <form onSubmit={submit} className="mt-6 grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-[1fr_220px_auto]">
                    <div>
                        <label htmlFor="articles-search" className="sr-only">Cari artikel</label>
                        <input
                            id="articles-search"
                            type="search"
                            value={q}
                            onChange={(e) => setQ(e.target.value)}
                            placeholder="Cari judul atau isi artikel..."
                            className="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                    <div>
                        <label htmlFor="articles-category" className="sr-only">Kategori</label>
                        <select
                            id="articles-category"
                            value={kategori}
                            onChange={(e) => setKategori(e.target.value)}
                            className="w-full cursor-pointer rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">Semua kategori</option>
                            {categories.map((c) => <option key={c.value} value={c.value}>{c.label}</option>)}
                        </select>
                    </div>
                    <button type="submit" className="cursor-pointer rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Terapkan
                    </button>
                </form>

                {posts.data.length === 0 ? (
                    <p className="mt-10 rounded-2xl bg-white p-10 text-center text-slate-500 ring-1 ring-slate-200">Tidak ada artikel yang cocok.</p>
                ) : (
                    <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {posts.data.map((post) => <ArticleCard key={post.id} post={post} />)}
                    </div>
                )}

                <Pagination paginator={posts} />
            </div>
        </PublicLayout>
    );
}
