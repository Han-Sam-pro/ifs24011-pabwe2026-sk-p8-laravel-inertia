import { Head, Link, useForm } from '@inertiajs/react';
import Button from '../../Components/Button';
import PublicLayout from '../../Layouts/PublicLayout';

const fields = [
    { id: 'register-name', name: 'name', label: 'Nama lengkap', type: 'text' },
    { id: 'register-username', name: 'username', label: 'Username', type: 'text' },
    { id: 'register-email', name: 'email', label: 'Email', type: 'email' },
    { id: 'register-password', name: 'password', label: 'Password (min. 8 karakter)', type: 'password' },
    { id: 'register-password-confirmation', name: 'password_confirmation', label: 'Ulangi password', type: 'password' },
];

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '', username: '', email: '', password: '', password_confirmation: '',
    });

    const submit = (event) => {
        event.preventDefault();
        post('/register');
    };

    return (
        <PublicLayout>
            <Head title="Daftar" />
            <div className="mx-auto max-w-md px-4 py-16">
                <div className="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
                    <h1 className="text-2xl font-bold text-slate-900">Buat akun penulis</h1>
                    <p className="mt-1 text-sm text-slate-600">Setelah daftar, Anda bisa menulis dan menerbitkan artikel.</p>

                    <form id="register-form" onSubmit={submit} className="mt-6 space-y-4">
                        {fields.map((f) => (
                            <div key={f.id}>
                                <label htmlFor={f.id} className="mb-1 block text-sm font-medium text-slate-700">{f.label}</label>
                                <input
                                    id={f.id}
                                    name={f.name}
                                    type={f.type}
                                    value={data[f.name]}
                                    onChange={(e) => setData(f.name, e.target.value)}
                                    className="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors[f.name] && <p className="mt-1 text-sm text-red-600">{errors[f.name]}</p>}
                            </div>
                        ))}
                        <Button id="register-submit" type="submit" loading={processing} className="w-full">Daftar</Button>
                    </form>

                    <p className="mt-6 text-center text-sm text-slate-600">
                        Sudah punya akun? <Link href="/login" className="cursor-pointer font-semibold text-indigo-700">Masuk</Link>
                    </p>
                </div>
            </div>
        </PublicLayout>
    );
}
