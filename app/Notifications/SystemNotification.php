<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Generic in-app (bell) notification.
 *
 * Stored on the `database` channel only — no mail is sent. The payload carries
 * everything the bell dropdown / notifications page needs to render a row and
 * link back to the related request.
 */
class SystemNotification extends Notification
{
    use Queueable;

    /** @var array */
    public $payload;

    /**
     * @param array $payload {
     *     title:   short headline (e.g. "MRS Approved")
     *     message: one-line detail (e.g. "Your MRS #123 was approved by MCD Planner.")
     *     url:     link to open when clicked (nullable)
     *     module:  MRS | IMF | PA
     *     status:  the resulting status string (nullable)
     *     icon:    optional icon hint for the UI (nullable)
     * }
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Delivery channels — database only (email intentionally disabled).
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Array representation stored in the `data` column.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title'   => $this->payload['title']   ?? 'Notification',
            'message' => $this->payload['message'] ?? '',
            'url'     => $this->payload['url']      ?? null,
            'module'  => $this->payload['module']   ?? null,
            'status'  => $this->payload['status']   ?? null,
            'icon'    => $this->payload['icon']     ?? null,
        ];
    }
}
