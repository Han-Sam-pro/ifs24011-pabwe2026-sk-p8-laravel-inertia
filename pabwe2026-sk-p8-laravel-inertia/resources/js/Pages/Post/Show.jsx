import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import ToastViewer from '../../Components/ToastViewer';
import Button from '../../Components/Button';
import { CategoryBadge, StatusBadge } from '../../Components/Badge';
import { ArrowLeftIcon } from '../../Components/Icons';
import { formatDate } from '../../lib/format';

export default function Show({ post }) {
    const togglePublish = () => router.patch(`/dashboard/posts/${post.id}/publish`, {}, { preserveScroll: true });

    return (
        <AppLayout title="Detail Artikel">
            <Head title={post.title} />

            <Link href="/dashboard/posts" className="mb-6 inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-indigo-700 hover:underline">
                <ArrowLeftIcon className="h-4 w-4" /> Kembali ke daftar
            </Link>

            <article className="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-10">
                <div className="flex flex-wrap items-center gap-2">
                    <CategoryBadge>{post.category}</CategoryBadge>
                    <StatusBadge published={post.is_published} />
                </div>
                <h2 className="mt-4 text-3xl font-extrabold text-slate-900">{post.title}</h2>
                <p className="mt-2 text-sm text-slate-500">
                    Dibuat {formatDate(post.created_at)} · Terbit {formatDate(post.published_at)}
                </p>
                {post.excerpt && <p className="mt-4 text-slate-600">{post.excerpt}</p>}
                {post.cover_url && <img src={post.cover_url} alt="" className="mt-6 max-h-96 w-full rounded-xl object-cover" />}

                <div className="mt-8">
                    <ToastViewer value={post.content} />
                </div>

                <div className="mt-10 flex flex-wrap gap-3 border-t border-slate-200 pt-6">
                    <Button variant="secondary" onClick={togglePublish}>
                        {post.is_published ? 'Jadikan Draf' : 'Publikasikan'}
                    </Button>
                </div>
            </article>
        </AppLayout>
    );
}
