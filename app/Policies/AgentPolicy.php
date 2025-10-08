<?php

namespace App\Policies;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AgentPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Agent $agent)
    {
        return $user->id === $agent->user_id;
    }

    public function update(User $user, Agent $agent)
    {
        return $user->id === $agent->user_id;
    }

    public function delete(User $user, Agent $agent)
    {
        return $user->id === $agent->user_id;
    }
}
