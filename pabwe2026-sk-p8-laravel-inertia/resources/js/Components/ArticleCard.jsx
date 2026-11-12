import { Link } from '@inertiajs/react';
import { CategoryBadge } from './Badge';
import { formatDate, initials } from '../lib/format';

export default function ArticleCard({ post }) {
    return (
        <article className="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 transition hover:shadow-md">
            {post.cover_url ? (
                <img src={post.cover_url} alt="" className="h-40 w-full object-cover" />
            ) : (
                <div className="flex h-40 items-center justify-center bg-gradient-to-br from-indigo-500 to-violet-600 text-3xl font-bold text-white/90">
                    {initials(post.title)}
                </div>
            )}
            <div className="flex flex-1 flex-col gap-3 p-5">
                <CategoryBadge>{post.category}</CategoryBadge>
                <h3 className="text-lg font-semibold leading-snug text-slate-900">
                    <Link href={`/articles/${post.slug}`} className="cursor-pointer hover:text-indigo-600">
                        {post.title}
                    </Link>
                </h3>
                {post.excerpt && <p className="line-clamp-3 text-sm text-slate-600">{post.excerpt}</p>}
                <p className="mt-auto text-xs text-slate-500">
                    {post.user?.name ?? 'Penulis'} · {formatDate(post.published_at)}
                </p>
            </div>
        </article>
    );
}
