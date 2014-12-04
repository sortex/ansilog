<?php
/**
 * Auth helper
 *
 * @package    Kohana/Auth
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Auth {

	public static $session_type = 'native';

	/**
	 * @var Auth  Static instance
	 */
	protected static $_instance;

	/**
	 * @var Session  Session object
	 */
	protected $_session;

	/**
	 * @var ArrayObject  Config array
	 */
	protected $_config;

	/**
	 * @var mixed  User The authenticated user loaded model
	 */
	protected $_auth_user;

	/**
	 * Singleton pattern
	 *
	 * @return  Auth
	 * @throws  Kohana_Exception
	 */
	public static function instance()
	{
		if ( ! isset(Auth::$_instance))
		{
			$config = Kohana::$config->load('auth');

			// Create a new session instance
			Auth::$_instance = new Auth($config);
		}

		return Auth::$_instance;
	}

	/**
	 * Loads Session and configuration options.
	 *
	 * @param   array|ArrayObject  $config  Config Options
	 */
	public function __construct($config = [])
	{
		// Save the config in the object
		$this->_config = $config;

		$this->_session = Session::instance(self::$session_type);
	}

	/**
	 * Sets and gets the config object from auth
	 *
	 * @param  null  $config
	 * @return $this|array|ArrayObject
	 */
	public function config($config = NULL)
	{
		if ($config === NULL)
		{
			return $this->_config;
		}

		$this->_config = $config;

		return $this;
	}

	/**
	 * Authorize a user entity
	 *
	 * @param   mixed   $user         Authenticated user loaded model
	 * @param   bool    $use_session  Use session to mark user?
	 * @return  $this
	 */
	public function authorize($user, $use_session = TRUE)
	{
		$this->_auth_user = $user;

		if ($use_session === TRUE)
		{
			// Regenerate session_id
			$this->_session->regenerate();

			// Store username in session
			$this->_session->set($this->_config['session_key'], $user);
		}

		return $this;
	}

	/**
	 * Sets and gets the auto-login cookie
	 *
	 * @param  string  $token
	 * @param  int     $lifetime
	 * @return $this
	 */
	public function remember($token = NULL, $lifetime = NULL)
	{
		$cookie_key = Arr::path($this->_config, 'autologin.cookie_key', 'authautologin');

		if ($token === NULL)
		{
			return Cookie::get($cookie_key);
		}

		if ($lifetime === NULL)
		{
			$lifetime = Arr::path($this->_config, 'autologin.lifetime', Date::DAY * 7);
		}

		Cookie::set($cookie_key, $token, $lifetime);

		return $this;
	}

	/**
	 * Gets the currently logged in user from the session.
	 * Returns NULL if no user is currently logged in.
	 *
	 * @param   mixed  $default  Default value to return if the user is currently not logged in.
	 * @return  mixed
	 */
	public function get_user($default = NULL)
	{
		if ($this->_auth_user)
		{
			return $this->_auth_user;
		}

		return $this->_session->get($this->_config['session_key'], $default);
	}

	/**
	 * Check if there is an active session
	 *
	 * @return  bool
	 */
	public function logged_in()
	{
		return ($this->get_user() !== NULL);
	}

	/**
	 * Log out a user by removing the related session variables.
	 *
	 * @param   bool  $destroy          Completely destroy the session
	 * @param   bool  $remove_remember  Removes the auto-login cookie
	 * @return  bool
	 */
	public function logout($destroy = FALSE, $remove_remember = FALSE)
	{
		if ($remove_remember === TRUE)
		{
			// Remove the auto-login cookie
			$cookie_key = Arr::path($this->_config, 'autologin.cookie_key', 'authautologin');
			Cookie::delete($cookie_key);
		}

		if ($destroy === TRUE)
		{
			// Destroy the session completely
			$this->_session->destroy();
		}
		else
		{
			// Remove the user from the session
			$this->_session->delete($this->_config['session_key']);

			// Regenerate session_id
			$this->_session->regenerate();
		}

		// Double check
		return ! $this->logged_in();
	}

}
