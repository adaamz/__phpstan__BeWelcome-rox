<?php

namespace App\Model;

use App\Entity\Member;
use App\Entity\PasswordReset;
use App\Utilities\ManagerTrait;
use Exception as Exception;

class MemberModel
{
    use ManagerTrait;

    /**
     * @param Member $member
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return string
     */
    public function generatePasswordResetToken(Member $member)
    {
        try {
            $token = random_bytes(32);
        } catch (Exception $e) {
            $token = openssl_random_pseudo_bytes(32);
        }
        $token = bin2hex($token);

        // Persist token into password reset table
        $passwordReset = new PasswordReset();
        $passwordReset
            ->setMember($member)
            ->setToken($token);
        $this->em->persist($passwordReset);
        $this->em->flush();

        return $token;
    }
}
