<?php

namespace BackSystem\Base\Orm\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

trait CreatedBy
{
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'created_by')]
    private ?UserInterface $createdBy;

    public function getCreatedBy(): ?UserInterface
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?UserInterface $member): self
    {
        $this->createdBy = $member;

        return $this;
    }
}
