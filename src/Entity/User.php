<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column]
    private ?\DateTime $dateOfRegister = null;

    #[ORM\Column]
    private ?\DateTime $dateOfBirth = null;

    #[ORM\Column]
    private ?bool $isVerified = null;

    #[ORM\Column]
    private ?bool $isBanned = null;

    #[ORM\Column(length: 255)]
    private ?string $pictureProfil = null;

    #[ORM\Column(length: 10)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 100)]
    private ?string $city = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?LevelRun $level = null;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $posts;

    /**
     * @var Collection<int, Topic>
     */
    #[ORM\OneToMany(targetEntity: Topic::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $topics;

    /**
     * @var Collection<int, InstantMessage>
     */
    #[ORM\OneToMany(targetEntity: InstantMessage::class, mappedBy: 'sender')]
    private Collection $instantMessagesSender;

    /**
     * @var Collection<int, InstantMessage>
     */
    #[ORM\OneToMany(targetEntity: InstantMessage::class, mappedBy: 'receiver')]
    private Collection $instantMessagesReceiver;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'organizer')]
    private Collection $events;

    /**
     * @var Collection<int, RegistrationEvent>
     */
    #[ORM\OneToMany(targetEntity: RegistrationEvent::class, mappedBy: 'user')]
    private Collection $registrationEvents;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->topics = new ArrayCollection();
        $this->instantMessagesSender = new ArrayCollection();
        $this->instantMessagesReceiver = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->registrationEvents = new ArrayCollection();
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

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

    public function setDateOfBirth(\DateTime $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

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

    public function getPictureProfil(): ?string
    {
        return $this->pictureProfil;
    }

    public function setPictureProfil(string $pictureProfil): static
    {
        $this->pictureProfil = $pictureProfil;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
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
     * @return Collection<int, InstantMessage>
     */
    public function getInstantMessagesSender(): Collection
    {
        return $this->instantMessagesSender;
    }

    public function addInstantMessageSender(InstantMessage $instantMessageSender): static
    {
        if (!$this->instantMessagesSender->contains($instantMessageSender)) {
            $this->instantMessagesSender->add($instantMessageSender);
            $instantMessageSender->setSender($this);
        }

        return $this;
    }

    public function removeInstantMessage(InstantMessage $instantMessageSender): static
    {
        if ($this->instantMessagesSender->removeElement($instantMessageSender)) {
            // set the owning side to null (unless already changed)
            if ($instantMessageSender->getSender() === $this) {
                $instantMessageSender->setSender(null);
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
}
