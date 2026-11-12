<?php

namespace Tests\Unit\Post;

use App\Features\Post\Models\Post;
use App\Features\Post\Policies\PostPolicy;
use App\Models\User;
use Tests\TestCase;

class PostPolicyTest extends TestCase
{
    public function test_only_the_owner_is_allowed_for_every_ability(): void
    {
        $owner = new User;
        $owner->id = 1;

        $other = new User;
        $other->id = 2;

        $post = new Post;
        $post->user_id = 1;

        $policy = new PostPolicy;

        foreach (['view', 'update', 'delete', 'publish', 'manageCover'] as $ability) {
            $this->assertTrue($policy->{$ability}($owner, $post), "{$ability} untuk pemilik");
            $this->assertFalse($policy->{$ability}($other, $post), "{$ability} untuk bukan pemilik");
        }
    }
}
