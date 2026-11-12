import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../../Layouts/AppLayout';
import Pagination from '../../../Components/Pagination';
import { DownloadIcon, TrashIcon } from '../../../Components/Icons';

export default function Index({ users }) {
    const changeRole = (user, role) => {
        router.patch(`/admin/users/${user.id}/role`, { role }, { preserveScroll: true });
    };

    const remove = (user) => {
        if (!window.confirm(`Hapus pengguna "${user.name}" beserta seluruh artikelnya?`)) return;
        router.delete(`/admin/users/${user.id}`, { preserveScroll: true });
    };

    return (
        <AppLayout title="Kelola Pengguna">
            <Head title="Kelola Pengguna" />

            <div className="overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th className="px-4 py-3">Nama</th>
                            <th className="px-4 py-3">Username</th>
                            <th className="px-4 py-3">Email</th>
                            <th className="px-4 py-3">Peran</th>
                            <th className="px-4 py-3 text-center">Artikel</th>
                            <th className="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {users.data.map((user) => (
                            <tr key={user.id}>
                                <td className="px-4 py-3 font-medium text-slate-900">{user.name}</td>
                                <td className="px-4 py-3 text-slate-600">{user.username}</td>
                                <td className="px-4 py-3 text-slate-600">{user.email}</td>
                                <td className="px-4 py-3">
                                    <select
                                        aria-label={`Peran ${user.name}`}
                                        value={user.role}
                                        onChange={(e) => changeRole(user, e.target.value)}
                                        className="cursor-pointer rounded-lg border-slate-300 py-1 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="member">Anggota</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <Link href={`/admin/users/${user.id}/posts`} className="cursor-pointer font-semibold text-indigo-700 hover:underline">
                                        {user.posts_count}
                                    </Link>
                                </td>
                                <td className="px-4 py-3">
                                    <div className="flex items-center justify-end gap-1">
                                        <a href={`/admin/users/${user.id}/posts/export`} className="cursor-pointer rounded-lg p-2 text-indigo-700 hover:bg-indigo-50" title="Ekspor artikel">
                                            <DownloadIcon className="h-4 w-4" />
                                        </a>
                                        <button type="button" onClick={() => remove(user)} className="cursor-pointer rounded-lg p-2 text-red-600 hover:bg-red-50" title="Hapus pengguna">
                                            <TrashIcon className="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <Pagination paginator={users} />
        </AppLayout>
    );
}
