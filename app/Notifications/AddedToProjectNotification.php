<?php

namespace App\Notifications;

use App\Models\Project\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AddedToProjectNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Project $project) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("You have been added to: {$this->project->name}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("You have been added to a new project.")
            ->line("Project: {$this->project->name}")
            ->when($this->project->description, function ($mail) {
                return $mail->line("Description: {$this->project->description}");
            })
            ->action('View Project', url("/projects/{$this->project->id}"))
            ->line('Welcome to the team!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'project_id'          => $this->project->id,
            'project_name'        => $this->project->name,
            'project_description' => $this->project->description,
            'message'             => "You were added to: {$this->project->name}",
        ];
    }
}
