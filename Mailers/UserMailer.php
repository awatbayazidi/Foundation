<?php namespace AwatBayazidi\Foundation\Mailers;

use AwatBayazidi\AtbAuth\Models\User;

class UserMailer extends Mailer
{

	public function sendWelcomeMessageTo(User $user)
	{
		$subject = 'Welcome to Larasocial';

		$view = 'email-alerts.registration-confirm';

		$data = [];

		return $this->sendTo($user, $subject, $view);
	}


	public function sendFriendRequestAlertTo(User $requestedUser, User $requesterUser)
	{
		$subject = 'Someone would like to be your friend';

		$view = 'email-alerts.friend-request';

		$data = ['userFirstname' => $requestedUser->firstname, 'requesterFirstname' => $requesterUser->firstname];

		return $this->sendTo($requestedUser, $subject, $view, $data);
	}	
}