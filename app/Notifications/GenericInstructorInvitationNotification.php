<?php

namespace App\Notifications;

use Domain\Entities\Models\Entity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString; // Needed for line breaks

class GenericInstructorInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Entity $entity;
    public string $committeeCode;

    /**
     * Create a new notification instance.
     *
     * @param  Entity  $entity  The entity sending the invitation
     * @param  string  $committeeCode  The code of the relevant committee (e.g., 'DIVING')
     */
    public function __construct(Entity $entity, string $committeeCode)
    {
        $this->entity = $entity;
        $this->committeeCode = $committeeCode;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable  The User model being notified
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable  The User model being notified
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        // Generate signed URLs for accept/reject actions
        $acceptUrl = URL::temporarySignedRoute(
            'instructor-invitations.accept', // Route name to be defined
            now()->addDays(7), // Link valid for 7 days
            [
                'entityId' => $this->entity->id,
                'userId' => $notifiable->id,
                'committeeCode' => $this->committeeCode,
            ]
        );

        $rejectUrl = URL::temporarySignedRoute(
            'instructor-invitations.reject', // Route name to be defined
            now()->addDays(7), // Link valid for 7 days
            [
                'entityId' => $this->entity->id,
                'userId' => $notifiable->id,
                'committeeCode' => $this->committeeCode,
            ]
        );

        $entityName = $this->entity->name;
        $entityLink = route('public.entity.show', $this->entity->id); // Assuming a public profile route exists
        $committeeDisplay = ucfirst(strtolower($this->committeeCode));

        return (new MailMessage)
            ->subject(__('Invitation: Associate with :entityName as Instructor/Leader', ['entityName' => $entityName]))
            ->greeting(__('Hello!'))
            ->line(new HtmlString(__(':entityName has invited you to associate with them as an Instructor & Dive Leader.', ['entityName' => "<a href='{$entityLink}' target='_blank'>{$entityName}</a>"])))
            ->line(__('Accepting this invitation will automatically associate your account with :entityName for all relevant Instructor & Dive Leader roles within the :committee committee for which you currently hold an active certification and license.', ['entityName' => $entityName, 'committee' => $committeeDisplay]))
            ->line(new HtmlString(
                // Accept Button (Blue)
                "<a href='{$acceptUrl}' style='display: inline-block; margin-right: 10px; padding: 10px 20px; background-color: #1a56db; color: white; text-align: center; text-decoration: none; border-radius: 5px; font-weight: bold;'>" . __('Accept Invitation') . '</a>' .
                    // Reject Button (Red)
                    "<a href='{$rejectUrl}' style='display: inline-block; padding: 10px 20px; background-color: #ef4444; color: white; text-align: center; text-decoration: none; border-radius: 5px; font-weight: bold;'>" . __('Reject Invitation') . '</a>'
            ))
            ->line(__('If you did not expect this invitation, you can ignore this email.'))
            ->salutation(new HtmlString(__('Regards') . ',<br>' . e(config('branding.primary.short_name', 'DF'))));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable  The User model being notified
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'entity_id' => $this->entity->id,
            'entity_name' => $this->entity->name,
            'committee_code' => $this->committeeCode,
            'message' => __('Invitation to associate with :entityName as an Instructor/Leader (:committee)..', ['entityName' => $this->entity->name, 'committee' => $this->committeeCode]),
        ];
    }
}
