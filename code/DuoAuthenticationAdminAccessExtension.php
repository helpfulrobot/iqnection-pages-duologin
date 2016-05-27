<?php

class DuoAuthenticationAdminAccessExtension extends Extension
{
	/**
	 * Makes sure the user is authenticated with Duo before allowing access to the admin
	 */
	public function onBeforeInit()
	{
		if (Member::CurrentUser())
		{
			// check duo authentication
			$controller = singleton('DuoAuthPage_Controller');
			if (!$controller->DuoAuthenticated() && DuoAuthPage_Controller::DuoEnabled())
			{
				return $this->owner->redirect('/DuoAdminSecurity');
			}
		}
	}
	
}