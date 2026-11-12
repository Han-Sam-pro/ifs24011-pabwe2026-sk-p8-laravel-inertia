import { router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import Button from '../Button';
import Modal from '../Modal';
import ToastEditor from '../ToastEditor';
import { PlusIcon, PencilIcon } from '../Icons';

/** Modal tambah/ubah artikel (layar penuh). post = null untuk mode tambah. */
export default function PostFormModal({ open, onClose, post = null, categories }) {
    const isEdit = post !== null;

    const { data, setData, post: submit, put, processing, errors, reset, clearErrors } = useForm({
        title: post?.title ?? '',
        category: post?.category ?? categories[0]?.value ?? 'Umum',
        excerpt: post?.excerpt ?? '',
        content: post?.content ?? '',
        is_published: post?.is_published ?? false,
    });

    const [uploading, setUploading] = useState(false);

    const save = () => {
        const options = {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                onClose();
            },
        };

        if (isEdit) {
            put(`/dashboard/posts/${post.id}`, options);
        } else {
            submit('/dashboard/posts', options);
        }
    };

    const uploadCover = (file) => {
        if (!file || !isEdit) return;
        setUploading(true);
        router.post(`/dashboard/posts/${post.id}/cover`, { cover: file }, {
            forceFormData: true,
            preserveScroll: true,
            onFinish: () => setUploading(false),
        });
    };

    const removeCover = () => {
        router.delete(`/dashboard/posts/${post.id}/cover`, { preserveScroll: true });
    };

    return (
        <Modal
            open={open}
            onClose={() => { clearErrors(); onClose(); }}
            title={isEdit ? 'Ubah Artikel' : 'Tambah Artikel'}
            footer={
                <>
                    <Button variant="light" onClick={() => { clearErrors(); onClose(); }} disabled={processing}>Batal</Button>
                    <Button variant="primary" onClick={save} loading={processing} className="bg-emerald-500 hover:bg-emerald-600">
                        {isEdit ? <PencilIcon className="h-4 w-4" /> : <PlusIcon className="h-4 w-4" />}
                        {isEdit ? 'Simpan Perubahan' : 'Simpan Artikel'}
                    </Button>
                </>
            }
        >
            <div className="mx-auto grid max-w-6xl gap-6 lg:grid-cols-[1fr_300px]">
                <div className="space-y-5">
                    <div>
                        <label htmlFor="post-title" className="mb-1 block text-sm font-medium text-slate-700">Judul</label>
                        <input id="post-title" value={data.title} onChange={(e) => setData('title', e.target.value)}
                            className="w-full rounded-lg border-slate-300 text-lg focus:border-indigo-500 focus:ring-indigo-500" />
                        {errors.title && <p className="mt-1 text-sm text-red-600">{errors.title}</p>}
                    </div>

                    <div>
                        <label htmlFor="post-excerpt" className="mb-1 block text-sm font-medium text-slate-700">Ringkasan</label>
                        <textarea id="post-excerpt" rows={2} value={data.excerpt} onChange={(e) => setData('excerpt', e.target.value)}
                            className="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" />
                        {errors.excerpt && <p className="mt-1 text-sm text-red-600">{errors.excerpt}</p>}
                    </div>

                    <div>
                        <span className="mb-1 block text-sm font-medium text-slate-700">Isi artikel (Markdown)</span>
                        <div className="overflow-hidden rounded-lg ring-1 ring-slate-300">
                            <ToastEditor initialValue={data.content} onChange={(md) => setData('content', md)} />
                        </div>
                        {errors.content && <p className="mt-1 text-sm text-red-600">{errors.content}</p>}
                    </div>
                </div>

                <aside className="space-y-5">
                    <div>
                        <label htmlFor="post-category" className="mb-1 block text-sm font-medium text-slate-700">Kategori</label>
                        <select id="post-category" value={data.category} onChange={(e) => setData('category', e.target.value)}
                            className="w-full cursor-pointer rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            {categories.map((c) => <option key={c.value} value={c.value}>{c.label}</option>)}
                        </select>
                        {errors.category && <p className="mt-1 text-sm text-red-600">{errors.category}</p>}
                    </div>

                    <div>
                        <label htmlFor="post-status" className="mb-1 block text-sm font-medium text-slate-700">Status</label>
                        <select id="post-status" value={data.is_published ? '1' : '0'}
                            onChange={(e) => setData('is_published', e.target.value === '1')}
                            className="w-full cursor-pointer rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="0">Draf</option>
                            <option value="1">Publik</option>
                        </select>
                    </div>

                    <div className="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
                        <p className="mb-3 text-sm font-semibold text-slate-700">Sampul</p>
                        {!isEdit && <p className="text-xs text-slate-500">Simpan artikel terlebih dahulu, lalu ubah artikel untuk mengunggah sampul.</p>}
                        {isEdit && (
                            <>
                                {post.cover_url
                                    ? <img src={post.cover_url} alt="Sampul saat ini" className="mb-3 h-36 w-full rounded-lg object-cover" />
                                    : <p className="mb-3 text-xs text-slate-500">Belum ada sampul.</p>}
                                <label className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-white px-3 py-2 text-sm font-medium text-indigo-700 ring-1 ring-slate-300 hover:bg-indigo-50">
                                    <input type="file" accept="image/jpeg,image/png,image/webp" className="sr-only"
                                        disabled={uploading} onChange={(e) => uploadCover(e.target.files?.[0])} />
                                    {uploading ? 'Mengunggah...' : 'Unggah sampul'}
                                </label>
                                {post.cover_url && (
                                    <button type="button" onClick={removeCover} className="ml-2 cursor-pointer text-sm font-medium text-red-600 hover:underline">
                                        Hapus sampul
                                    </button>
                                )}
                            </>
                        )}
                    </div>
                </aside>
            </div>
        </Modal>
    );
}
