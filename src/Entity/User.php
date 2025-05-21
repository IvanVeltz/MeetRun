<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private bool $isVerified = false;

    /**
     * @var Collection<int, Topic>
     */
    #[ORM\OneToMany(targetEntity: Topic::class, mappedBy: 'user')]
    private Collection $topics;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'user')]
    private Collection $posts;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'followers')]
    private Collection $follow;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'follow')]
    private Collection $followers;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'organizer', orphanRemoval: true)]
    private Collection $events;

    /**
     * @var Collection<int, RegistrationEvent>
     */
    #[ORM\OneToMany(targetEntity: RegistrationEvent::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $registrationEvents;

    /**
     * @var Collection<int, InstantMessage>
     */
    #[ORM\OneToMany(targetEntity: InstantMessage::class, mappedBy: 'sender', orphanRemoval: true)]
    private Collection $instantMessagesSender;

    /**
     * @var Collection<int, InstantMessage>
     */
    #[ORM\OneToMany(targetEntity: InstantMessage::class, mappedBy: 'receiver', orphanRemoval: true)]
    private Collection $instantMessagesReceiver;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column]
    private ?\DateTime $dateOfRegister = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateOfBirth = null;

    #[ORM\Column]
    private ?bool $isBanned = null;

    #[ORM\Column(length: 255)]
    private ?string $pictureProfilUrl = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;

    public function __construct()
    {
        $this->topics = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->follow = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->registrationEvents = new ArrayCollection();
        $this->instantMessagesSender = new ArrayCollection();
        $this->instantMessagesReceiver = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

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

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, Topic>
     */
    public function getTopics(): Collection
    {
        return $this->topics;
    }

    public function addTopic(Topic $topic): static
    {
        if (!$this->topics->contains($topic)) {
            $this->topics->add($topic);
            $topic->setUser($this);
        }

        return $this;
    }

    public function removeTopic(Topic $topic): static
    {
        if ($this->topics->removeElement($topic)) {
            // set the owning side to null (unless already changed)
            if ($topic->getUser() === $this) {
                $topic->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getFollow(): Collection
    {
        return $this->follow;
    }

    public function addFollow(self $follow): static
    {
        if (!$this->follow->contains($follow)) {
            $this->follow->add($follow);
        }

        return $this;
    }

    public function removeFollow(self $follow): static
    {
        $this->follow->removeElement($follow);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function addFollower(self $follower): static
    {
        if (!$this->followers->contains($follower)) {
            $this->followers->add($follower);
            $follower->addFollow($this);
        }

        return $this;
    }

    public function removeFollower(self $follower): static
    {
        if ($this->followers->removeElement($follower)) {
            $follower->removeFollow($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setOrganizer($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getOrganizer() === $this) {
                $event->setOrganizer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RegistrationEvent>
     */
    public function getRegistrationEvents(): Collection
    {
        return $this->registrationEvents;
    }

    public function addRegistrationEvent(RegistrationEvent $registrationEvent): static
    {
        if (!$this->registrationEvents->contains($registrationEvent)) {
            $this->registrationEvents->add($registrationEvent);
            $registrationEvent->setUser($this);
        }

        return $this;
    }

    public function removeRegistrationEvent(RegistrationEvent $registrationEvent): static
    {
        if ($this->registrationEvents->removeElement($registrationEvent)) {
            // set the owning side to null (unless already changed)
            if ($registrationEvent->getUser() === $this) {
                $registrationEvent->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, InstantMessage>
     */
    public function getInstantMessagesSender(): Collection
    {
        return $this->instantMessagesSender;
    }

    public function addInstantMessagesSender(InstantMessage $instantMessagesSender): static
    {
        if (!$this->instantMessagesSender->contains($instantMessagesSender)) {
            $this->instantMessagesSender->add($instantMessagesSender);
            $instantMessagesSender->setSender($this);
        }

        return $this;
    }

    public function removeInstantMessagesSender(InstantMessage $instantMessagesSender): static
    {
        if ($this->instantMessagesSender->removeElement($instantMessagesSender)) {
            // set the owning side to null (unless already changed)
            if ($instantMessagesSender->getSender() === $this) {
                $instantMessagesSender->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, InstantMessage>
     */
    public function getInstantMessagesReceiver(): Collection
    {
        return $this->instantMessagesReceiver;
    }

    public function addInstantMessagesReceiver(InstantMessage $instantMessagesReceiver): static
    {
        if (!$this->instantMessagesReceiver->contains($instantMessagesReceiver)) {
            $this->instantMessagesReceiver->add($instantMessagesReceiver);
            $instantMessagesReceiver->setReceiver($this);
        }

        return $this;
    }

    public function removeInstantMessagesReceiver(InstantMessage $instantMessagesReceiver): static
    {
        if ($this->instantMessagesReceiver->removeElement($instantMessagesReceiver)) {
            // set the owning side to null (unless already changed)
            if ($instantMessagesReceiver->getReceiver() === $this) {
                $instantMessagesReceiver->setReceiver(null);
            }
        }

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getDateOfRegister(): ?\DateTime
    {
        return $this->dateOfRegister;
    }

    public function setDateOfRegister(\DateTime $dateOfRegister): static
    {
        $this->dateOfRegister = $dateOfRegister;

        return $this;
    }

    public function getDateOfBirth(): ?\DateTime
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTime $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function isBanned(): ?bool
    {
        return $this->isBanned;
    }

    public function setIsBanned(bool $isBanned): static
    {
        $this->isBanned = $isBanned;

        return $this;
    }

    public function getPictureProfilUrl(): ?string
    {
        return $this->pictureProfilUrl;
    }

    public function setPictureProfilUrl(string $pictureProfilUrl): static
    {
        $this->pictureProfilUrl = $pictureProfilUrl;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }
}
