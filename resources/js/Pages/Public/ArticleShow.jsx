import { Head, Link } from '@inertiajs/react';
import { CategoryBadge } from '../../Components/Badge';
import ToastViewer from '../../Components/ToastViewer';
import PublicLayout from '../../Layouts/PublicLayout';
import { formatDate } from '../../lib/format';
import { CommunityCard } from './Home';

export default function ArticleShow({ post }) {
    return (
        <PublicLayout>
            <Head title={post.title}><meta name="description" content={post.excerpt || post.title} /></Head>

            <article className="w-full px-4 py-10 sm:px-8 lg:px-16">
                <Link href="/articles" className="mb-6 inline-block cursor-pointer text-sm font-semibold text-indigo-700 hover:underline">
                    ← Kembali ke daftar artikel
                </Link>

                <CategoryBadge>{post.category}</CategoryBadge>
                <h1 className="mt-4 text-3xl font-extrabold leading-tight text-slate-900 sm:text-4xl">{post.title}</h1>
                <p className="mt-3 text-sm text-slate-500">
                    Ditulis oleh {post.user?.name ?? 'Penulis'} · {formatDate(post.published_at)}
                </p>

                {post.cover_url && <img src={post.cover_url} alt="" className="mt-8 max-h-[480px] w-full rounded-2xl object-cover" />}

                <div className="mt-8 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-10">
                    <ToastViewer value={post.content} />
                </div>
            </article>

            <div className="px-4 pb-16 sm:px-8 lg:px-16">
                <CommunityCard />
            </div>
        </PublicLayout>
    );
}
