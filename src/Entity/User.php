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
#[UniqueEntity(fields: ['email'], message: 'Cet email a déjà été utilisé')]
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

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column]
    private ?\DateTime $dateOfRegister = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateOfBirth = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isBanned = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pictureProfilUrl = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $sexe = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?LevelRun $level = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column (nullable:true)]
    private ?string $password = null;

    #[ORM\Column (nullable: true)]
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

    /**
     * @var Collection<int, Follow>
     */
    #[ORM\OneToMany(targetEntity: Follow::class, mappedBy: 'userSource', orphanRemoval: true)]
    private Collection $follows;

    /**
     * @var Collection<int, Follow>
     */
    #[ORM\OneToMany(targetEntity: Follow::class, mappedBy: 'userTarget', orphanRemoval: true)]
    private Collection $followers;

    #[ORM\Column]
    private ?bool $createdByGoogle = false;

    #[ORM\Column]
    private ?bool $firstConnection = false;

    #[ORM\Column]
    private ?bool $deleted = false;



    public function __construct()
    {
        $this->topics = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->follows = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->registrationEvents = new ArrayCollection();
        $this->instantMessagesSender = new ArrayCollection();
        $this->instantMessagesReceiver = new ArrayCollection();
        $this->follows = new ArrayCollection();
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

    public function setPictureProfilUrl(?string $pictureProfilUrl): static
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

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): static
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(?string $sexe): static
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getLevel(): ?LevelRun
    {
        return $this->level;
    }

    public function setLevel(?LevelRun $level): static
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
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

    public function getAge(): ?int
    {
        if (!$this->dateOfBirth) {
            return null;
        }

        $now = new \DateTime();
        return $this->dateOfBirth->diff($now)->y;
    }

    /**
     * @return Collection<int, Follow>
     */
    public function getFollows(): Collection
    {
        return $this->follows;
    }

    public function addFollow(Follow $follow): static
    {
        if (!$this->follows->contains($follow)) {
            $this->follows->add($follow);
            $follow->setUserSource($this);
        }

        return $this;
    }

    public function removeFollow(Follow $follow): static
    {
        if ($this->follows->removeElement($follow)) {
            // set the owning side to null (unless already changed)
            if ($follow->getUserSource() === $this) {
                $follow->setUserSource(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Follow>
     */
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function addFollower(Follow $follower): static
    {
        if (!$this->followers->contains($follower)) {
            $this->followers->add($follower);
            $follower->setUserTarget($this);
        }

        return $this;
    }

    public function removeFollower(Follow $follower): static
    {
        if ($this->followers->removeElement($follower)) {
            // set the owning side to null (unless already changed)
            if ($follower->getUserTarget() === $this) {
                $follower->setUserTarget(null);
            }
        }

        return $this;
    }

    public function isCreatedByGoogle(): ?bool
    {
        return $this->createdByGoogle;
    }

    public function setCreatedByGoogle(bool $createdByGoogle): static
    {
        $this->createdByGoogle = $createdByGoogle;

        return $this;
    }

    public function isFirstConnection(): ?bool
    {
        return $this->firstConnection;
    }

    public function setFirstConnection(bool $firstConnection): static
    {
        $this->firstConnection = $firstConnection;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }
}
