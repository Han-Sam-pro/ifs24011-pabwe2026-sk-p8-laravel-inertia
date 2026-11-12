import { Head, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Button from '../../Components/Button';

export default function Edit({ profile }) {
    const { data, setData, patch, processing, errors, reset } = useForm({
        name: profile.name,
        username: profile.username,
        email: profile.email,
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        patch('/profile', { preserveScroll: true, onSuccess: () => reset('password', 'password_confirmation') });
    };

    const field = (id, label, key, type = 'text') => (
        <div>
            <label htmlFor={id} className="mb-1 block text-sm font-medium text-slate-700">{label}</label>
            <input id={id} type={type} value={data[key]} onChange={(e) => setData(key, e.target.value)}
                className="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" />
            {errors[key] && <p className="mt-1 text-sm text-red-600">{errors[key]}</p>}
        </div>
    );

    return (
        <AppLayout title="Profil">
            <Head title="Profil" />
            <form onSubmit={submit} className="max-w-xl space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
                {field('profile-name', 'Nama', 'name')}
                {field('profile-username', 'Username', 'username')}
                {field('profile-email', 'Email', 'email', 'email')}
                <p className="pt-2 text-sm text-slate-500">Kosongkan password jika tidak ingin mengubahnya.</p>
                {field('profile-password', 'Password baru', 'password', 'password')}
                {field('profile-password-confirmation', 'Ulangi password baru', 'password_confirmation', 'password')}
                <Button id="profile-submit" type="submit" loading={processing}>Simpan Profil</Button>
            </form>
        </AppLayout>
    );
}
