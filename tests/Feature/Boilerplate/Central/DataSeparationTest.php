<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\User;
use App\Models\Team;
use App\Models\TeamInvitation;
use Laravel\Jetstream\Contracts\InvitesTeamMembers;

test('a tenant cannot access users of another tenant', function() {
    $tenant1 = $this->createTenant(['email' => 'tenant@foo.test']);
    $tenant2 = $this->createTenant(['email' => 'tenant@bar.test']);

    tenancy()->initialize($tenant1);

    $user = User::factory()->create();

    expect(User::firstWhere('email', $user->email)->is($user))->toBeTrue();

    tenancy()->initialize($tenant2);

    expect(User::firstWhere('email', $user->email))->toBeNull();
});

test('a tenant cannot access posts of another tenant', function() {
    $tenant1 = $this->createTenant(['email' => 'tenant@foo.test']);
    $tenant2 = $this->createTenant(['email' => 'tenant@bar.test']);

    tenancy()->initialize($tenant1);

    $post = Post::create([
        'title' => 'foobar',
        'body' => 'test',
        'user_id' => User::first()->id,
    ]);

    expect(Post::firstWhere('title', $post->title)->is($post))->toBeTrue();

    tenancy()->initialize($tenant2);

    expect(Post::firstWhere('title', $post->title))->toBeNull();
});

test('a tenant cannot access teams of another tenant', function() {
    $tenant1 = $this->createTenant(['email' => 'tenant@foo.test']);
    $tenant2 = $this->createTenant(['email' => 'tenant@bar.test']);

    $user = $tenant1->run(fn () => User::factory()->create());

    tenancy()->initialize($tenant1);

    $team = $tenant1->run(fn () => $user->ownedTeams()->create(['personal_team' => true, 'name' => "first tenant's team"]));

    expect(Team::firstWhere('name', $team->name))->not()->toBeNull();

    tenancy()->initialize($tenant2);

    expect(Team::firstWhere('name', $team->name))->toBeNull();
});

test('a tenant cannot access team invitations of another tenant', function() {
    $tenant1 = $this->createTenant(['email' => 'tenant@foo.test']);
    $tenant2 = $this->createTenant(['email' => 'tenant@bar.test']);

    $user = $tenant1->run(fn () => User::factory()->create());
    $team = $tenant1->run(fn () => $user->ownedTeams()->create(['personal_team' => true, 'name' => 'Team']));

    tenancy()->initialize($tenant1);

    app(InvitesTeamMembers::class)->invite(
        $user,
        $team,
        $invitationEmail = 'testing@email.test',
        'admin',
    );

    expect(TeamInvitation::firstWhere('email', $invitationEmail))->not()->toBeNull();

    tenancy()->initialize($tenant2);

    expect(TeamInvitation::firstWhere('email', $invitationEmail))->toBeNull();
});
