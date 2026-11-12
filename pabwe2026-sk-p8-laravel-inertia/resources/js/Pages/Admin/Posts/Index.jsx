import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../../Layouts/AppLayout';
import Pagination from '../../../Components/Pagination';
import { CategoryBadge, StatusBadge } from '../../../Components/Badge';
import { DownloadIcon } from '../../../Components/Icons';
import { formatDate } from '../../../lib/format';

/** Monitoring artikel (read-only). Dipakai untuk semua artikel atau artikel satu pengguna. */
export default function Index({ posts, owner }) {
    const exportHref = owner ? `/admin/users/${owner.id}/posts/export` : '/admin/posts/export';

    return (
        <AppLayout title={owner ? `Artikel milik ${owner.name}` : 'Semua Artikel'}>
            <Head title={owner ? `Artikel ${owner.name}` : 'Semua Artikel'} />

            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <p className="text-sm text-slate-500">Mode monitoring: artikel hanya dapat dilihat, tidak dapat diubah atau dihapus.</p>
                <div className="flex gap-2">
                    {owner && <Link href="/admin/users" className="cursor-pointer rounded-lg px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">Kembali</Link>}
                    <a href={exportHref} className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50">
                        <DownloadIcon className="h-4 w-4" /> Ekspor Excel
                    </a>
                </div>
            </div>

            <div className="overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th className="px-4 py-3">Judul</th>
                            <th className="px-4 py-3">Kategori</th>
                            <th className="px-4 py-3">Penulis</th>
                            <th className="px-4 py-3">Status</th>
                            <th className="px-4 py-3">Diubah</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {posts.data.length === 0 && (
                            <tr><td colSpan={5} className="px-4 py-10 text-center text-slate-500">Belum ada artikel.</td></tr>
                        )}
                        {posts.data.map((post) => (
                            <tr key={post.id}>
                                <td className="px-4 py-3 font-medium text-slate-900">{post.title}</td>
                                <td className="px-4 py-3"><CategoryBadge>{post.category}</CategoryBadge></td>
                                <td className="px-4 py-3 text-slate-600">{post.user?.name}</td>
                                <td className="px-4 py-3"><StatusBadge published={post.is_published} /></td>
                                <td className="px-4 py-3 text-slate-600">{formatDate(post.updated_at)}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <Pagination paginator={posts} />
        </AppLayout>
    );
}
