<?php

namespace App\Policies;

use App\Models\TeamMemberDocument;
use App\Models\User;

class TeamMemberPolicy
{
    public function viewProfile(User $user, User $member): bool
    {
        return $user->isGestor() && $member->isLiderado();
    }

    public function manageDocuments(User $user, User $member): bool
    {
        return $user->isGestor() && $member->isLiderado();
    }

    public function deleteDocument(User $user, TeamMemberDocument $document): bool
    {
        return $user->isGestor();
    }
}
