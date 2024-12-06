<?php

declare(strict_types=1);

namespace App\Auth\Domain;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_LOGIN', fields: ['login'])]
#[ORM\Table(name: "auth_user")]
#[Exclude]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 180)]
    private string $login;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private int $customerId;

    #[ORM\Column]
    private int $version;

    /**
     * @param int $customerId
     */
    public function __construct(int $customerId)
    {
        $this->customerId = $customerId;
        $this->version = 0;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;

        return $this;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function incrementVersion(): void
    {
        $this->version += 1;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->login;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        $this->roles = array_unique($roles);

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
