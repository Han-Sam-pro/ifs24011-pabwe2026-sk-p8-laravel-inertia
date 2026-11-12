import { Head, router } from '@inertiajs/react';
import { useCallback, useState } from 'react';
import { usePage } from '@inertiajs/react';
import AiGenerateModal from '../../Components/Post/AiGenerateModal';
import ImportModal from '../../Components/Post/ImportModal';
import PostFormModal from '../../Components/Post/PostFormModal';
import Button from '../../Components/Button';
import { CategoryBadge, StatusBadge } from '../../Components/Badge';
import KebabMenu from '../../Components/KebabMenu';
import Pagination from '../../Components/Pagination';
import { DownloadIcon, EyeIcon, PencilIcon, PlusIcon, SparklesIcon, TrashIcon, UploadIcon } from '../../Components/Icons';
import AppLayout from '../../Layouts/AppLayout';
import { formatDate } from '../../lib/format';

function StatCard({ label, value, tone }) {
    return (
        <div className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p className="text-sm font-medium text-slate-500">{label}</p>
            <p className={`mt-2 text-3xl font-bold ${tone}`}>{value}</p>
        </div>
    );
}

export default function Index({ posts, stats, categories }) {
    const { flash } = usePage().props;
    const report = flash?.importReport;
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState(null);
    const [aiOpen, setAiOpen] = useState(false);
    const [importOpen, setImportOpen] = useState(false);

    const openCreate = () => { setEditing(null); setFormOpen(true); };
    const openEdit = (post) => { setEditing(post); setFormOpen(true); };
    const closeForm = useCallback(() => setFormOpen(false), []);

    const remove = (post) => {
        if (!window.confirm(`Hapus artikel "${post.title}"? Tindakan ini tidak dapat dibatalkan.`)) return;
        router.delete(`/dashboard/posts/${post.id}`, { preserveScroll: true });
    };

    const togglePublish = (post) => {
        router.patch(`/dashboard/posts/${post.id}/publish`, {}, { preserveScroll: true });
    };

    return (
        <AppLayout title="Artikel Saya">
            <Head title="Artikel Saya" />

            <div className="mb-6 flex flex-wrap gap-3">
                <Button onClick={openCreate}><PlusIcon className="h-4 w-4" /> Tambah Artikel</Button>
                <Button variant="secondary" onClick={() => setAiOpen(true)}><SparklesIcon className="h-4 w-4" /> Buat dengan AI</Button>
                <Button variant="secondary" onClick={() => setImportOpen(true)}><UploadIcon className="h-4 w-4" /> Impor Excel</Button>
                <a href="/dashboard/posts/export" className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50">
                    <DownloadIcon className="h-4 w-4" /> Ekspor Excel
                </a>
            </div>

            {report && (
                <section className="mb-6 rounded-2xl bg-white p-5 ring-1 ring-slate-200">
                    <h2 className="font-semibold text-slate-900">Hasil impor: {report.imported} artikel tersimpan</h2>
                    {report.failed.length > 0 ? (
                        <>
                            <p className="mt-1 text-sm text-red-600">{report.failed.length} baris gagal diimpor:</p>
                            <ul className="mt-2 list-disc space-y-1 pl-5 text-sm text-slate-700">
                                {report.failed.map((row) => (
                                    <li key={row.row}>Baris {row.row}: {row.errors.join(' ')}</li>
                                ))}
                            </ul>
                        </>
                    ) : (
                        <p className="mt-1 text-sm text-emerald-600">Semua baris berhasil diimpor.</p>
                    )}
                </section>
            )}

            <div className="grid gap-4 sm:grid-cols-3">
                <StatCard label="Total Artikel" value={stats.total} tone="text-slate-900" />
                <StatCard label="Total Selesai" value={stats.published} tone="text-emerald-600" />
                <StatCard label="Belum Selesai" value={stats.draft} tone="text-amber-600" />
            </div>

            <section className="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                {posts.data.length === 0 ? (
                    <p className="p-10 text-center text-slate-500">Belum ada artikel. Mulai dengan menekan “Tambah Artikel”.</p>
                ) : (
                    <ul className="divide-y divide-slate-200">
                        {posts.data.map((post) => (
                            <li key={post.id} className="flex flex-wrap items-center gap-4 px-5 py-4">
                                <div className="min-w-0 flex-1">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <p className="truncate font-semibold text-slate-900">{post.title}</p>
                                        <StatusBadge published={post.is_published} />
                                    </div>
                                    <div className="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                        <CategoryBadge>{post.category}</CategoryBadge>
                                        <span>Diubah {formatDate(post.updated_at)}</span>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    onClick={() => togglePublish(post)}
                                    className="cursor-pointer rounded-lg px-3 py-1.5 text-sm font-semibold text-indigo-700 ring-1 ring-indigo-200 hover:bg-indigo-50"
                                >
                                    {post.is_published ? 'Jadikan Draf' : 'Publikasikan'}
                                </button>

                                <KebabMenu
                                    items={[
                                        { label: 'Detail', icon: EyeIcon, onSelect: () => router.get(`/dashboard/posts/${post.id}`) },
                                        { label: 'Ubah', icon: PencilIcon, onSelect: () => openEdit(post) },
                                        { label: 'Hapus', icon: TrashIcon, tone: 'danger', onSelect: () => remove(post) },
                                    ]}
                                />
                            </li>
                        ))}
                    </ul>
                )}
            </section>

            <Pagination paginator={posts} />

            <PostFormModal key={editing?.id ?? 'new'} open={formOpen} onClose={closeForm} post={editing} categories={categories} />
            <AiGenerateModal open={aiOpen} onClose={() => setAiOpen(false)} />
            <ImportModal open={importOpen} onClose={() => setImportOpen(false)} />

        </AppLayout>
    );
}
