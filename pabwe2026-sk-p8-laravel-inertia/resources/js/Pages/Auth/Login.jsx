import { Head, Link, useForm } from '@inertiajs/react';
import Button from '../../Components/Button';
import PublicLayout from '../../Layouts/PublicLayout';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({ username: '', password: '', remember: false });

    const submit = (event) => {
        event.preventDefault();
        post('/login', { preserveScroll: true, onFinish: () => setData('password', '') });
    };

    return (
        <PublicLayout>
            <Head title="Masuk" />
            <div className="mx-auto max-w-md px-4 py-16">
                <div className="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
                    <h1 className="text-2xl font-bold text-slate-900">Masuk</h1>
                    <p className="mt-1 text-sm text-slate-600">Gunakan username dan password Anda.</p>

                    <form id="login-form" onSubmit={submit} className="mt-6 space-y-4">
                        <div>
                            <label htmlFor="login-username" className="mb-1 block text-sm font-medium text-slate-700">Username</label>
                            <input
                                id="login-username"
                                name="username"
                                type="text"
                                autoComplete="username"
                                value={data.username}
                                onChange={(e) => setData('username', e.target.value)}
                                className="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            {errors.username && <p className="mt-1 text-sm text-red-600">{errors.username}</p>}
                        </div>
                        <div>
                            <label htmlFor="login-password" className="mb-1 block text-sm font-medium text-slate-700">Password</label>
                            <input
                                id="login-password"
                                name="password"
                                type="password"
                                autoComplete="current-password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                className="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                        <label className="flex cursor-pointer items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" checked={data.remember} onChange={(e) => setData('remember', e.target.checked)} className="rounded border-slate-300" />
                            Ingat saya
                        </label>
                        <Button id="login-submit" type="submit" loading={processing} className="w-full">
                            Masuk
                        </Button>
                    </form>

                    <p className="mt-6 text-center text-sm text-slate-600">
                        Belum punya akun? <Link href="/register" className="cursor-pointer font-semibold text-indigo-700">Daftar</Link>
                    </p>
                </div>
            </div>
        </PublicLayout>
    );
}
