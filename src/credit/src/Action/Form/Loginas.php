<?php

namespace Credit\Action\Form;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use User;
use Credit;
use Zend\Session\Container;
use Zend\Authentication\AuthenticationService;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Diactoros\Response\JsonResponse;

class Loginas
{

    public function __construct(
            User\Model\UserTable $userTable,
            Credit\Model\CreditUserTable $creditUserTable,
            AuthenticationService $authenticationService,
            UrlHelper $urlHelper
        )
    {
        $this->userTable = $userTable;
        $this->creditUserTable = $creditUserTable;
        $this->authenticationService = $authenticationService;
        $this->urlHelper = $urlHelper;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {

        //for checking if something went wrong later
        $errorMessage = false;

        //get userId from URL
        $userId = (int) $request->getAttribute('userId');

        if($userId > 0) {
            //get who we're logged in as at the moment
            $currentUser = $request->getAttribute(User\Model\User::class);
			//var_dump($currentUser);

            if(!is_null($currentUser)) {

                //open up $_SESSION['currentUser'] (the Zend way)
                $originalUser = new Container('admin_user');
				$originalUser->getManager()->getStorage()->clear('admin_user');

                //check to see currentUser exists, otherwise initialise it and set logged in user 
                if(is_null($originalUser->currentUser) || array() == $originalUser->currentUser) {
                    $originalUser->currentUser = $currentUser;
                    $originalUser->topLevelRole = $currentUser->getRole();
                }

                //get newUser information
                $newUser = $this->creditUserTable->oneById($userId);
				
                $loginAsUser = false;

				//var_dump($originalUser->currentUser);
                //see whether currentUser has permission to login an as newUser
                //NOTE admin cannot login as another admin
				if($originalUser->currentUser->getRole() == "Rbac\\Role\\Administrator") {
                    //LOGIN AS USER
                    $loginAsUser = true;

                } else {
                    $errorMessage = "You do not have permission to log in as credit user";
                }


                if($loginAsUser) {

                    //add the current logged in user to the login chain
                    if(is_null($originalUser->userChain) || array() == $originalUser->userChain) {
                        $originalUser->userChain = array($originalUser->currentUser);#
                        $originalUser->currentUser = $newUser;
                    } else {
                        //make sure we don't add the same user more than once to the session array
                        $add = true;
                        foreach($originalUser->userChain as $user) {
                            if($user->getId() == $originalUser->currentUser->getId()) {
                                $add = false;
                            }
                        }

                        if($add) {
                            $originalUser->userChain[] = $currentUser;
                        }
                        $originalUser->currentUser = $newUser;
                    }

					$session_user = new Container('credit_user');

                    //log existing user out
					$session_user->getManager()->getStorage()->clear('credit_user');

                    //push new user to the login state
                    $session_user->data = $newUser;
					$session_user->username = $newUser->getUsername();

					if($newUser->getRole() == "Rbac\\Role\\PersonalUser") {
						$newUserStudent = $this->userTable->oneById($newUser->student[0]);
						$originalAdmin = new Container('OriginalUser');

						if(is_null($originalAdmin->currentUser) || array() == $originalAdmin->currentUser) {
							$originalAdmin->currentUser = $currentUser;
							$originalAdmin->topLevelRole = $currentUser->getRole();
						}

	                    //add the current logged in user to the login chain
						if(is_null($originalAdmin->userChain) || array() == $originalAdmin->userChain) {
							$originalAdmin->userChain = array($originalAdmin->currentUser);#
							$originalAdmin->currentUser = $newUserStudent;
						} else {
							//make sure we don't add the same user more than once to the session array
							$add = true;
							foreach($originalAdmin->userChain as $user) {
								if($user->getId() == $originalAdmin->currentUser->getId()) {
									$add = false;
								}
							}

							if($add) {
								$originalAdmin->userChain[] = $currentUser;
							}
							$originalAdmin->currentUser = $newUserStudent;
						}

						//log existing user out
						$this->authenticationService->clearIdentity();
						//push new user to the login state
						$storage = $this->authenticationService->getStorage();
						$storage->write($newUserStudent);
					}

                    return new RedirectResponse(($this->urlHelper)('credit/home'));
                }

            } else {
                $errorMessage = "You are not logged in";
            }

            
        } else {
            $errorMessage = "No valid user ID supplied";
        }

        return new JsonResponse(['errorMessage' => $errorMessage]);

    }
}
