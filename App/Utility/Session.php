<?php

namespace App\Utility;

/**
 * Session class for managing flash messages.
 */
class Session
{
    /**
     * Set a flash message.
     *
     * @param string $key The key for the message.
     * @param mixed $value The message content.
     * @return void
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a flash message and clear it.
     *
     * @param string $key The key for the message.
     * @return mixed The message content or null if not found.
     */
    public static function get($key)
    {
        if (isset($_SESSION[$key])) {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $value;
        }
        return null;
    }
}
