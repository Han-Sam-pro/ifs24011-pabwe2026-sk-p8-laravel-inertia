<?php

namespace App\Features\Admin\Services;

use App\Models\User;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminUserService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->withCount('posts')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @throws DomainException
     */
    public function changeRole(User $actor, User $target, string $role): void
    {
        $this->ensureNotSelf($actor, $target, 'Anda tidak dapat mengubah peran akun sendiri.');

        if (! in_array($role, [User::ROLE_MEMBER, User::ROLE_ADMIN], true)) {
            throw new DomainException('Peran tidak dikenal.');
        }

        $target->role = $role;
        $target->save();
    }

    /**
     * @throws DomainException
     */
    public function delete(User $actor, User $target): void
    {
        $this->ensureNotSelf($actor, $target, 'Anda tidak dapat menghapus akun sendiri.');

        $target->delete();
    }

    private function ensureNotSelf(User $actor, User $target, string $message): void
    {
        if ($actor->is($target)) {
            throw new DomainException($message);
        }
    }
}
