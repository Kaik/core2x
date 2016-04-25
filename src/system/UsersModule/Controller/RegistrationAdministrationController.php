<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\UsersModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationProviders;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\RegistrationEvents;

/**
 * Class RegistrationAdministrationController
 * @Route("/admin/registration")
 */
class RegistrationAdministrationController extends AbstractController
{
    /**
     * @Route("/list/{startnum}")
     * @Theme("admin")
     * @Template
     * @param Request $request
     * @param integer $startnum
     * @return array
     */
    public function listAction(Request $request, $startnum = 0)
    {
        if (!$this->hasPermission('ZikulaUsersModule', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $this->get('zikulausersmodule.helper.registration_helper')->purgeExpired();

        $limit = $this->getVar(UsersConstant::MODVAR_ITEMS_PER_PAGE, UsersConstant::DEFAULT_ITEMS_PER_PAGE);
        $users = $this->get('zikula_users_module.user_repository')->query(
            ['activated' => UsersConstant::ACTIVATED_PENDING_REG],
            ['user_regdate' => 'DESC'],
            $limit,
            $startnum
        );

        return [
            'moderationOrder' => $this->getVar(UsersConstant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE, UsersConstant::DEFAULT_REGISTRATION_APPROVAL_SEQUENCE),
            'actionsHelper' => $this->get('zikula_users_module.helper.administration_actions'),
            'verificationRepo' => $this->get('zikula_users_module.user_verification_repository'),
            'pager' => [
                'count' => $users->count(),
                'limit' => $limit
            ],
            'users' => $users
        ];
    }

    /**
     * @Route("/modify/{user}", requirements={"user" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template()
     * @param Request $request
     * @param UserEntity $user
     * @return Response
     */
    public function modifyAction(Request $request, UserEntity $user)
    {
        if (!$this->hasPermission('ZikulaUsersModule::', $user->getUname() . "::" . $user->getUid(), ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm('Zikula\UsersModule\Form\Type\ModifyRegistrationType',
            $user, ['translator' => $this->get('translator.default')]
        );

        $originalUser = clone $user;
        $form->handleRequest($request);

        $event = new GenericEvent($form->getData(), array(), new ValidationProviders());
        $this->get('event_dispatcher')->dispatch(RegistrationEvents::REGISTRATION_VALIDATE_MODIFY, $event);
        $validators = $event->getData();
        $hook = new ValidationHook($validators);
        $this->get('hook_dispatcher')->dispatch(RegistrationEvents::HOOK_REGISTRATION_VALIDATE, $hook);
        $validators = $hook->getValidators();

        if ($form->isValid() && !$validators->hasErrors()) {
            if ($form->get('submit')->isClicked()) {
                /** @var UserEntity $user */
                $user = $form->getData();
                $this->get('doctrine')->getManager()->flush($user);
                $eventArgs = [
                    'action'    => 'setVar',
                    'field'     => 'uname',
                    'attribute' => null,
                ];
                $eventData = ['old_value' => $originalUser->getUname()];
                $updateEvent = new GenericEvent($user, $eventArgs, $eventData);
                $this->get('event_dispatcher')->dispatch(RegistrationEvents::UPDATE_REGISTRATION, $updateEvent);
                if ($user->getEmail() != $originalUser->getEmail()) {
                    $approvalOrder = $this->getVar('moderation_order', UsersConstant::APPROVAL_BEFORE);
                    if (!(bool)$user->getAttributeValue('_Users_isVerified') && (($approvalOrder != UsersConstant::APPROVAL_BEFORE) || $originalUser->isApproved())) {
                        $verificationSent = $this->get('zikulausersmodule.helper.registration_verification_helper')->sendVerificationCode(null, $user->getUid(), true);
                        if (!$verificationSent) {
                            $this->addFlash('error', $this->__('Could not resend verification code.'));
                        }
                    }
                }
                $this->get('event_dispatcher')->dispatch(RegistrationEvents::REGISTRATION_PROCESS_MODIFY, new GenericEvent($user));
                $this->get('hook_dispatcher')->dispatch(RegistrationEvents::HOOK_REGISTRATION_PROCESS, new ProcessHook($user->getUid()));

                $this->addFlash('status', $this->__("Done! Saved user's account information."));

                return $this->redirectToRoute('zikulausersmodule_admin_displayregistration', ['uid' => $user->getUid()]);
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));

                return $this->redirectToRoute('zikulausersmodule_admin_viewregistrations');
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }
}