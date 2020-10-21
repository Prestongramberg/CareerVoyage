<?php

namespace App\Service;

use App\Entity\User;

/**
 * Class NotificationPreferencesManager
 * @package App\Service
 */
class NotificationPreferencesManager
{
    /**
     * @see https://www.sdss.org/dr12/algorithms/bitmasks/
     */
    const MASK_DISABLE_EVENT_NOTIFICATION_EMAILS = 1;
    const MASK_DISABLE_CHAT_NOTIFICATION_EMAILS = 2;
    const MASK_DISABLE_ALL_NOTIFICATION_EMAILS = 4;

    public static $choices = [
        'Disable Event Notification Emails' => self::MASK_DISABLE_EVENT_NOTIFICATION_EMAILS,
        'Disable Chat Notification Emails' => self::MASK_DISABLE_CHAT_NOTIFICATION_EMAILS,
        'Disable All Notification Emails' => self::MASK_DISABLE_ALL_NOTIFICATION_EMAILS
    ];

    /**
     * Resolves the grants for a given user
     *
     * @param User $user
     * @param bool $stringify
     * @return array
     */
    public function resolveGrants(User $user, $stringify = false) {

        $result = [];
        if (($user->getNotificationPreferenceMask() & self::MASK_DISABLE_EVENT_NOTIFICATION_EMAILS) == self::MASK_DISABLE_EVENT_NOTIFICATION_EMAILS) $result[] = 'disable_event_notification_emails';
        if (($user->getNotificationPreferenceMask() & self::MASK_DISABLE_CHAT_NOTIFICATION_EMAILS) == self::MASK_DISABLE_CHAT_NOTIFICATION_EMAILS) $result[] = 'disable_chat_notification_emails';
        if (($user->getNotificationPreferenceMask() & self::MASK_DISABLE_ALL_NOTIFICATION_EMAILS) == self::MASK_DISABLE_ALL_NOTIFICATION_EMAILS) $result[] = 'disable_all_notification_emails';

        return $stringify ? implode(", ", $result) : $result;
    }

    /**
     * Returns the mask for a set of grants
     *
     * @param array|null $grants
     * @return int
     *
     */
    public function resolveMasks(?array $grants) {
        $bit = 0;
        if ($grants != null && count($grants)) {
            if (in_array('disable_event_notification_emails', $grants)) $bit |= self::MASK_DISABLE_EVENT_NOTIFICATION_EMAILS;
            if (in_array('disable_chat_notification_emails', $grants)) $bit |= self::MASK_DISABLE_CHAT_NOTIFICATION_EMAILS;
            if (in_array('disable_all_notification_emails', $grants)) $bit |= self::MASK_DISABLE_ALL_NOTIFICATION_EMAILS;
        }

        return $bit;
    }

    /**
     * Determines whether or not a given bitmask permission is disabled for a given user
     * @param int $bit
     * @param User $user
     * @return bool
     */
    public function isNotificationDisabled(int $bit, User $user): bool {

        if(($bit & $user->getNotificationPreferenceMask()) == $bit) {
            return true;
        }

        return false;
    }
}