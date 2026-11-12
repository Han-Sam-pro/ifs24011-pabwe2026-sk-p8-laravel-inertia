import { router } from '@inertiajs/react';
import { useState } from 'react';
import Button from '../Button';
import Modal from '../Modal';
import { UploadIcon } from '../Icons';

export default function ImportModal({ open, onClose }) {
    const [file, setFile] = useState(null);
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState(null);

    const submit = () => {
        if (!file) return;
        setProcessing(true);
        setError(null);
        router.post('/dashboard/posts/import', { file }, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => { setFile(null); onClose(); },
            onError: (errors) => setError(errors.file ?? 'Impor gagal.'),
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <Modal
            open={open}
            onClose={onClose}
            title="Impor Artikel dari Excel"
            footer={
                <>
                    <Button variant="light" onClick={onClose}>Batal</Button>
                    <Button variant="primary" onClick={submit} loading={processing} disabled={!file} className="bg-emerald-500 hover:bg-emerald-600">
                        <UploadIcon className="h-4 w-4" /> Impor
                    </Button>
                </>
            }
        >
            <div className="mx-auto max-w-2xl space-y-5 rounded-2xl bg-white p-6 ring-1 ring-slate-200">
                <p className="text-sm text-slate-600">
                    Berkas harus memiliki baris header dengan kolom: <strong>Judul</strong> (wajib), <strong>Kategori</strong> (wajib),
                    serta opsional <strong>Ringkasan</strong>, <strong>Konten</strong>, dan <strong>Status</strong> (Publik / Draf).
                </p>
                <input id="import-file" type="file" accept=".xlsx,.csv" onChange={(e) => setFile(e.target.files?.[0] ?? null)}
                    className="block w-full cursor-pointer text-sm text-slate-700 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:font-semibold file:text-white hover:file:bg-indigo-700" />
                {error && <p className="text-sm text-red-600">{error}</p>}
            </div>
        </Modal>
    );
}
