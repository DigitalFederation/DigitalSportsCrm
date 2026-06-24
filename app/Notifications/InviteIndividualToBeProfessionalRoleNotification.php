<?php

namespace App\Notifications;

use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityAthlete;
use Domain\Entities\Models\EntityProfessionalRole;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InviteIndividualToBeProfessionalRoleNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private EntityProfessionalRole|EntityAthlete $association;

    private Entity $entity;

    private string $role;

    private string $committee_code;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(EntityProfessionalRole|EntityAthlete $association, Entity $entity, string $role, string $committee_code)
    {
        $this->association = $association;
        $this->entity = $entity;
        $this->role = $role;
        $this->committee_code = $committee_code;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(mixed $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $roleLabel = strtolower($this->association->role_name ?? $this->association->sport_name.' athlete');
        $url = $this->role != 'ATHLETE' && $this->role != 'COACH'
            ? route('individual.'.strtolower($this->role).'.index', strtolower($this->committee_code))
            : route('individual.'.strtolower($this->role).'.index');

        return (new MailMessage)
            ->subject(__('notifications.invite_individual_professional.subject', ['role' => $roleLabel.' of '.$this->entity->name]))
            ->greeting(__('notifications.invite_individual_professional.greeting', ['name' => $this->association->individual_name]))
            ->line(__('notifications.invite_individual_professional.line_invited', ['role' => $roleLabel, 'entity' => $this->entity->name]))
            ->action(__('notifications.invite_individual_professional.action'), url($url))
            ->line(__('notifications.invite_individual_professional.line_thanks'))
            ->salutation(__('notifications.invite_individual_professional.salutation', ['app' => config('app.name')]));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'message' => __('notifications.invite_individual_professional.database', [
                'role' => $this->association->role_name,
                'entity' => $this->entity->name,
            ]),
            'link' => $this->role != 'ATHLETE' && $this->role != 'COACH' ? route('individual.'.strtolower($this->role).'.index', strtolower($this->committee_code)) : route('individual.'.strtolower($this->role).'.index'),
        ];
    }
}
