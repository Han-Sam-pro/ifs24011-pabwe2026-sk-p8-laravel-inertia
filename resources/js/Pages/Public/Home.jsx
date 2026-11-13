import { Head, Link } from '@inertiajs/react';
import ArticleCard from '../../Components/ArticleCard';
import Button from '../../Components/Button';
import PublicLayout from '../../Layouts/PublicLayout';

export default function Home({ latest }) {
    return (
        <PublicLayout>
            <Head title="Beranda"><meta name="description" content="Beranda Blog Pabwe: artikel terbaru tentang teknologi, pendidikan, gaya hidup, bisnis, dan opini." /></Head>

            <section className="bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-600 px-4 py-20 text-white">
                <div className="mx-auto max-w-4xl text-center">
                    <p className="text-sm font-semibold uppercase tracking-widest text-indigo-200">Editorial komunitas</p>
                    <h1 className="mt-4 text-4xl font-extrabold leading-tight sm:text-5xl">
                        Tulis, bagikan, dan temukan ide dari sesama penulis.
                    </h1>
                    <p className="mx-auto mt-5 max-w-2xl text-indigo-100">
                        Teknologi, pendidikan, gaya hidup, bisnis, dan opini—semua dalam satu tempat.
                    </p>
                    <div className="mt-8 flex flex-wrap justify-center gap-3">
                        <Link href="/register" className="cursor-pointer rounded-lg">
                            <Button variant="light">Mulai Menulis</Button>
                        </Link>
                        <Link href="/login" className="cursor-pointer rounded-lg">
                            <Button variant="secondary" className="bg-transparent text-white ring-white/40 hover:bg-white/10">Masuk</Button>
                        </Link>
                    </div>
                </div>
            </section>

            <section className="mx-auto max-w-6xl px-4 py-14">
                <div className="mb-8 flex items-end justify-between gap-4">
                    <h2 className="text-2xl font-bold text-slate-900">Artikel terbaru</h2>
                    <Link href="/articles" className="cursor-pointer text-sm font-semibold text-indigo-700 hover:underline">Lihat semua</Link>
                </div>

                {latest.length === 0 ? (
                    <p className="rounded-2xl bg-white p-10 text-center text-slate-500 ring-1 ring-slate-200">Belum ada artikel yang diterbitkan.</p>
                ) : (
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {latest.map((post) => <ArticleCard key={post.id} post={post} />)}
                    </div>
                )}
            </section>

            <CommunityCard />
        </PublicLayout>
    );
}

export function CommunityCard() {
    return (
        <section className="mx-auto max-w-4xl px-4 pb-16">
            <div className="rounded-2xl bg-slate-900 p-8 text-center text-white">
                <h2 className="text-2xl font-bold">Ingin ikut menulis?</h2>
                <p className="mt-2 text-slate-300">Bergabunglah dengan komunitas penulis. Gratis dan mudah.</p>
                <div className="mt-6 flex flex-wrap justify-center gap-3">
                    <Link href="/register" className="cursor-pointer"><Button variant="light">Daftar</Button></Link>
                    <Link href="/login" className="cursor-pointer"><Button variant="secondary" className="bg-transparent text-white ring-white/40 hover:bg-white/10">Masuk</Button></Link>
                </div>
            </div>
        </section>
    );
}
