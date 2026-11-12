<?php

namespace Tests\Feature\Middleware;

use App\Features\Post\Models\Post;
use App\Http\Middleware\EnsurePostOwnership;
use App\Http\Middleware\EnsureRole;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function requestWithRoute(array $parameters): Request
    {
        $request = Request::create('/test', 'GET');

        $route = new Route(['GET'], '/test', ['uses' => fn () => null]);
        $route->bind($request);

        foreach ($parameters as $name => $value) {
            $route->setParameter($name, $value);
        }

        $request->setRouteResolver(fn () => $route);

        return $request;
    }

    public function test_guest_is_redirected_to_login_by_ensure_role(): void
    {
        $response = (new EnsureRole)->handle(
            Request::create('/admin', 'GET'),
            fn () => new Response('ok'),
            'admin',
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('login'), $response->getTargetUrl());
    }

    public function test_owner_passes_ensure_post_ownership(): void
    {
        $owner = User::factory()->create();
        $post = Post::factory()->for($owner)->create();

        $this->actingAs($owner);

        $request = $this->requestWithRoute(['post' => $post]);

        $response = (new EnsurePostOwnership)->handle($request, fn () => new Response('ok'));

        $this->assertSame('ok', $response->getContent());
    }

    public function test_ensure_post_ownership_resolves_post_id_from_route(): void
    {
        $owner = User::factory()->create();
        $post = Post::factory()->for($owner)->create();

        $this->actingAs($owner);

        $request = $this->requestWithRoute(['post' => $post->id]);

        (new EnsurePostOwnership)->handle($request, fn () => new Response('ok'));

        $this->assertInstanceOf(Post::class, $request->route('post'));
        $this->assertTrue($request->route('post')->is($post));
    }

    public function test_ensure_post_ownership_denies_other_users(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::factory()->for($owner)->create();

        $this->actingAs($other);

        $this->expectException(AuthorizationException::class);

        (new EnsurePostOwnership)->handle(
            $this->requestWithRoute(['post' => $post->id]),
            fn () => new Response('ok'),
        );
    }
}
