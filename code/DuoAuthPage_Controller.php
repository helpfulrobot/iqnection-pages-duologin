<?php


class DuoAuthPage_Controller extends Page_Controller
{
	private static $allowed_actions = array(
		'duoauthenticate',
		'douauthorize'
	);
	
	public $SessionKey = '_douauth';
	public $URLSegment = 'DuoAdminSecurity';
	
	public function PageCSS()
	{
		return array_merge(
			parent::PageJS(),
			array(
				'iq-duologin/vendor/css/Duo-Frame.css'
			)
		);
	}
		
	public function PageJS()
	{
		return array_merge(
			parent::PageJS(),
			array(
				'iq-duologin/vendor/js/Duo-Web-v2.js'
			)
		);
	}
	
	public static function DuoKeys()
	{
		$Keys = array();
		if (!$Keys['IKEY'] = Config::inst()->get('DuoLogin', 'IKEY')) return false;
		if (!$Keys['SKEY'] = Config::inst()->get('DuoLogin', 'SKEY')) return false;
		if (!$Keys['AKEY'] = Config::inst()->get('DuoLogin', 'AKEY')) return false;
		if (!$Keys['HOST'] = Config::inst()->get('DuoLogin', 'HOST')) return false;
		return $Keys;
	}
	
	public static function DuoEnabled()
	{
		return (self::DuoKeys()) ? true : false;
	}
	
	public function DuoAuthenticated()
	{
		return (Session::get($this->SessionKey) == $this->AuthenticationUsername());
	}
	
	public function PostLink()
	{
		$Link = parent::AbsoluteLink('douauthorize');
		$Link = preg_replace('/'.get_class($this).'/',$this->URLSegment,$Link);
		return $Link;
	}
	
	public function index()
	{
		if ($this->DuoAuthenticated()) return $this->redirect($this->SuccessRedirectURL());
		return $this->redirect('DuoAdminSecurity/duoauthenticate');
	}
	
	public function duoauthenticate()
	{
		if ($this->DuoAuthenticated()) return $this->redirect($this->SuccessRedirectURL());
		require_once(dirname(dirname(__FILE__)).'/vendor/src/Web.php');
		 /*
         * Perform secondary auth, generate sig request, then load up Duo
         * javascript and iframe.
         */
		$Keys = self::DuoKeys();

        $sig_request = Duo\Web::signRequest($Keys['IKEY'], $Keys['SKEY'], $Keys['AKEY'], $this->AuthenticationUsername());

		return $this->Customise(array(
			'Host' => $Keys['HOST'],
			'sig_request' => $sig_request
		))->renderWith(array('Security_login','DuoAuthPage'));
	}
	
	public function douauthorize()
	{
		if (isset($_POST['sig_response'])) {
			$Keys = self::DuoKeys();
			/*
			 * Verify sig response and log in user. Make sure that verifyResponse
			 * returns the username we logged in with. You can then set any
			 * cookies/session data for that username and complete the login process.
			 */
			$resp = Duo\Web::verifyResponse($Keys['IKEY'], $Keys['SKEY'], $Keys['AKEY'], $_POST['sig_response']);
			if ($resp === $this->AuthenticationUsername()) 
			{
				Session::set($this->SessionKey,$resp);
				return $this->redirect($this->SuccessRedirectURL());
			}
		}
		return $this->redirect($this->Link('duoauthenticate'));
	}
	
	public function AuthenticationUsername()
	{
		return Member::CurrentUser()->Email;
	}
	
	public function SuccessRedirectURL()
	{
		return '/admin/';
	}
	
	public function LogoutLink()
	{
		return '/Security/logout?BackURL='.$this->SuccessRedirectURL();
	}
}