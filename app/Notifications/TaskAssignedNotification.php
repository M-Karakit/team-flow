<?php

namespace App\Notifications;

use App\Models\Task\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Task $task) {}

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
            ->subject("You have been assigned a task: {$this->task->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("You have been assigned a new task.")
            ->line("Task: {$this->task->title}")
            ->line("Project: {$this->task->project->name}")
            ->line("Priority: {$this->task->priority}")
            ->when($this->task->due_date, function ($mail) {
                return $mail->line("Due Date: {$this->task->due_date->format('Y-m-d')}");
            })
            ->action('View Task', url("/tasks/{$this->task->id}"))
            ->line('Please make sure to complete it before the due date.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id'      => $this->task->id,
            'task_title'   => $this->task->title,
            'project_id'   => $this->task->project_id,
            'project_name' => $this->task->project->name,
            'priority'     => $this->task->priority,
            'due_date'     => $this->task->due_date?->format('Y-m-d'),
            'message'      => "You were assigned: {$this->task->title}",
        ];
    }
}
