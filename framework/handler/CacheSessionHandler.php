<?php

namespace Framework\Handler;

use SessionHandler;
use Framework\App;
use Framework\Core\CacheAdapter;
use Framework\Core\Cache;


/**
 * 自定义缓存Session处理器
 * Class CacheSessionHandler
 */
class CacheSessionHandler extends SessionHandler {

    /** @var null|CacheAdapter CacheAdapter Instance */
    public $cache = null;

    /** @var string session prefix */
    private $sessionIdPrefix = '__SS';

    /**
     * register custom session handler
     * @param App $app
     */
    public function register(App $app) {
        $this->app = $app;

        // 设置自定义session处理器
        if ($app->config->sessionAdapter) {
            $this->cache = Cache::instance($app->config->sessionAdapter);
            $this->cache && $this->cache->isAvailable() && session_set_save_handler($this, true);
        }

        // 初始化session
        session_start();
    }

    /**
     * Return a new session ID
     * @link https://php.net/manual/en/sessionhandler.create-sid.php
     * @return string <p>A session ID valid for the default session handler.</p>
     * @since 5.5.1
     */
    public function create_sid() {
        return parent::create_sid();
    }

    /**
     * Initialize session
     * @link https://php.net/manual/en/sessionhandler.open.php
     * @param string $savePath The path where to store/retrieve the session.
     * @param string $sessionName The session name.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function open($savePath, $sessionName) {
        return true;
    }

    /**
     * Read session data
     * @link https://php.net/manual/en/sessionhandler.read.php
     * @param string $sessionId The session id to read data for.
     * @return string <p>
     * Returns an encoded string of the read data.
     * If nothing was read, it must return an empty string.
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function read($sessionId) {
        $session = $this->cache->get($this->getSessionId($sessionId));
        if (is_null($session) || $session === false) {
            $session = '';
        }
        return $session;
    }

    /**
     * Write session data
     * @link https://php.net/manual/en/sessionhandler.write.php
     * @param string $sessionId The session id.
     * @param string $sessionData <p>
     * The encoded session data. This data is the
     * result of the PHP internally encoding
     * the $_SESSION superglobal to a serialized
     * string and passing it as this parameter.
     * Please note sessions use an alternative serialization method.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function write($sessionId, $sessionData) {
        return $this->cache->set($this->getSessionId($sessionId), $sessionData, (int)config('sessionExpires'));
    }

    /**
     * Close the session
     * @link https://php.net/manual/en/sessionhandler.close.php
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function close() {
        return true;
    }

    /**
     * Destroy a session
     * @link https://php.net/manual/en/sessionhandler.destroy.php
     * @param string $sessionId The session ID being destroyed.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function destroy($sessionId) {
        return $this->cache->delete($this->getSessionId($sessionId));
    }

    /**
     * Cleanup old sessions
     * @link https://php.net/manual/en/sessionhandler.gc.php
     * @param int $maxLifeTime <p>
     * Sessions that have not updated for
     * the last maxlifetime seconds will be removed.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function gc($maxLifeTime) {
        return true;
    }

    /**
     * @param string $sessionId
     * @return string
     */
    private function getSessionId($sessionId) {
        return $this->sessionIdPrefix . $sessionId;
    }
}
