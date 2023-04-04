<?php

namespace BackSystem\Base\Orm\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

trait DeletedBy
{
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'deleted_by')]
    private ?UserInterface $deletedBy;

    public function getDeletedBy(): ?UserInterface
    {
        return $this->deletedBy;
    }

    public function setDeletedBy(?UserInterface $member): self
    {
        $this->deletedBy = $member;

        return $this;
    }
}
