import { router, useForm, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import Button from '../Button';
import Modal from '../Modal';
import { CategoryBadge } from '../Badge';
import { SparklesIcon } from '../Icons';

export default function AiGenerateModal({ open, onClose }) {
    const { flash } = usePage().props;
    const [drafts, setDrafts] = useState([]);
    const [saving, setSaving] = useState(false);
    const { data, setData, post, processing, errors } = useForm({ jumlah: 3 });

    // Hasil generate datang lewat flash session setelah redirect.
    useEffect(() => {
        if (flash?.aiDrafts?.length) setDrafts(flash.aiDrafts);
    }, [flash?.aiDrafts]);

    const generate = (event) => {
        event.preventDefault();
        post('/dashboard/posts/ai-generate', { preserveScroll: true, preserveState: true });
    };

    const saveAll = () => {
        setSaving(true);
        router.post('/dashboard/posts/ai-save', { items: drafts }, {
            preserveScroll: true,
            onSuccess: () => { setDrafts([]); onClose(); },
            onFinish: () => setSaving(false),
        });
    };

    return (
        <Modal
            open={open}
            onClose={onClose}
            title="Buat Draf dengan AI"
            footer={
                <>
                    <Button variant="light" onClick={onClose}>Tutup</Button>
                    <Button variant="primary" onClick={saveAll} loading={saving} disabled={drafts.length === 0}
                        className="bg-emerald-500 hover:bg-emerald-600">
                        Simpan {drafts.length > 0 ? `${drafts.length} draf` : 'semua'}
                    </Button>
                </>
            }
        >
            <div className="mx-auto max-w-3xl space-y-6">
                <form onSubmit={generate} className="flex flex-wrap items-end gap-3 rounded-2xl bg-white p-5 ring-1 ring-slate-200">
                    <div>
                        <label htmlFor="ai-jumlah" className="mb-1 block text-sm font-medium text-slate-700">Jumlah artikel (1–5)</label>
                        <input id="ai-jumlah" type="number" min={1} max={5} value={data.jumlah}
                            onChange={(e) => setData('jumlah', Number(e.target.value))}
                            className="w-32 rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" />
                        {errors.jumlah && <p className="mt-1 text-sm text-red-600">{errors.jumlah}</p>}
                    </div>
                    <Button id="ai-generate-submit" type="submit" loading={processing}>
                        <SparklesIcon className="h-4 w-4" />
                        {processing ? 'Membuat draf...' : 'Generate'}
                    </Button>
                </form>

                {drafts.length === 0 && <p className="text-center text-sm text-slate-500">Belum ada draf. Tentukan jumlah lalu klik Generate.</p>}

                <ul className="space-y-4">
                    {drafts.map((draft, index) => (
                        <li key={index} className="rounded-2xl bg-white p-5 ring-1 ring-slate-200">
                            <CategoryBadge>{draft.category}</CategoryBadge>
                            <h3 className="mt-2 text-lg font-semibold text-slate-900">{draft.title}</h3>
                            {draft.excerpt && <p className="mt-1 text-sm text-slate-600">{draft.excerpt}</p>}
                            <p className="mt-3 line-clamp-4 whitespace-pre-line text-sm text-slate-500">{draft.content}</p>
                        </li>
                    ))}
                </ul>
            </div>
        </Modal>
    );
}
