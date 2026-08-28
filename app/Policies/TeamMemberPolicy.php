<?php

namespace App\Policies;

use App\Models\TeamMemberDocument;
use App\Models\User;

class TeamMemberPolicy
{
    public function viewProfile(User $user, User $member): bool
    {
        return $this->manages($user, $member);
    }

    public function manageDocuments(User $user, User $member): bool
    {
        return $this->manages($user, $member);
    }

    public function deleteDocument(User $user, TeamMemberDocument $document): bool
    {
        return $this->manages($user, $document->user);
    }

    private function manages(User $user, User $member): bool
    {
        return $user->isGestor() && $member->isLiderado() && (int) $member->manager_id === (int) $user->id;
    }
}
