<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Controller;

use CategoryUtil;
use ModUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use System;
use Zikula\Core\Controller\AbstractController;
use ZLanguage;

/**
 * User controllers for the categories module.
 */
class UserController extends AbstractController
{
    /**
     * @Route("")
     *
     * Main user function.
     *
     * @param Request $request
     *
     * @return Response|RedirectResponse symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the module
     */
    public function indexAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $referer = $request->server->get('HTTP_REFERER');
        if (strpos($referer, '/categories') === false) {
            $request->getSession()->set('categories_referer', $referer);
        }

        $allowed = $this->getVar('allowusercatedit', 0);
        if ($allowed) {
            return $this->redirectToRoute('zikulacategoriesmodule_user_edituser');
        }

        $this->addFlash('error', $this->__('Sorry! User-owned category editing has not been enabled. This feature can be enabled by the site administrator.'));

        return $this->responseForErrorMessage();
    }

    /**
     * Route not needed here because method is legacy-only
     *
     * legacy main user function
     *
     * @deprecated since 1.4.0 @see indexAction()
     *
     * @return RedirectResponse
     */
    public function mainAction()
    {
        @trigger_error('The zikulcategoriesmodule_user_main action is deprecated. please use the index action instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
    }

    /**
     * @Route("/edit")
     * @Template
     *
     * Edit category for a simple, non-recursive set of categories.
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the document root
     */
    public function editAction(Request $request)
    {
        $docroot = $request->query->get('dr', 0);
        $cid = $request->query->get('cid', 0);

        if (!$this->hasPermission('ZikulaCategoriesModule::category', "ID::$docroot", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $referer = $request->headers->get('referer');
        if (strpos($referer, 'categories') === false) {
            $request->getSession()->set('categories_referer', $referer);
        }

        $editCat = [];

        if (!$docroot) {
            $this->addFlash('error', $this->__("Error! The URL contains an invalid 'document root' parameter."));

            return $this->responseForErrorMessage();
        }
        if ($docroot == 1) {
            $this->addFlash('error', $this->__("Error! The root directory cannot be modified in 'user' mode"));

            return $this->responseForErrorMessage();
        }

        if (is_int((int)$docroot) && $docroot > 0) {
            $rootCat = CategoryUtil::getCategoryByID($docroot);
        } else {
            $rootCat = CategoryUtil::getCategoryByPath($docroot);
            if (!$rootCat) {
                $rootCat = CategoryUtil::getCategoryByPath($docroot, 'ipath');
            }
        }

        // now check if someone is trying edit another user's categories
        $userRoot = $this->getVar('userrootcat', 0);
        if ($userRoot) {
            $userRootCat = CategoryUtil::getCategoryByPath($userRoot);
            if ($userRootCat) {
                $userRootCatIPath = $userRootCat['ipath'];
                $rootCatIPath = $rootCat['ipath'];
                if (strpos($rootCatIPath, $userRootCatIPath) !== false) {
                    if (!$this->hasPermission('Categories::category', "ID::$docroot", ACCESS_ADMIN)) {
                        $userRootCatPath = $userRootCat['path'];
                        $rootCatPath = $rootCat['path'];
                        if (strpos($rootCatPath, $userRootCatPath) === false) {
                            //! %s represents the root path (id), passed in the url
                            $this->addFlash('error', $this->__f("Error! It looks like you are trying to edit another user's categories. Only site administrators can do that (%s).", ['%s' => $docroot]));

                            return $this->responseForErrorMessage();
                        }
                    }
                }
            }
        }

        if ($cid) {
            $editCat = CategoryUtil::getCategoryByID($cid);
            if ($editCat['is_locked']) {
                $this->addFlash('error', $this->__f('Notice: The administrator has locked the category \'%category\' (ID \'%id\'). You cannot edit or delete it.', ['%category' => $editCat['name'], '%id' => $cid]));

                return $this->responseForErrorMessage();
            }
        }

        if (!$rootCat) {
            $this->addFlash('error', $this->__f('Error! Cannot access root directory (%s).', ['%s' => $docroot]));

            return $this->responseForErrorMessage();
        }
        if ($editCat && !$editCat['is_leaf']) {
            $this->addFlash('error', $this->__f('Error! The specified category is not a leaf-level category (%s).', ['%s' => $cid]));

            return $this->responseForErrorMessage();
        }
        if ($editCat && !CategoryUtil::isDirectSubCategory($rootCat, $editCat)) {
            $this->addFlash('error', $this->__f('Error! The specified category is not a child of the document root (%docroot; %id).', ['%docroot' => $docroot, '%id' => $cid]));

            return $this->responseForErrorMessage();
        }

        $allCats = CategoryUtil::getSubCategoriesForCategory($rootCat, false, false, false, true, true);

        $attributes = isset($editCat['__ATTRIBUTES__']) ? $editCat['__ATTRIBUTES__'] : [];

        return [
            'rootCat' => $rootCat,
            'category' => $editCat,
            'attributes' => $attributes,
            'allCats' => $allCats,
            'languages' => ZLanguage::getInstalledLanguages(),
            'userlanguage' => ZLanguage::getLanguageCode(),
            'referer' => $request->getSession()->get('categories_referer'),
            'csrfToken' => $this->get('zikula_core.common.csrf_token_handler')->generate()
        ];
    }

    /**
     * @Route("/edituser")
     *
     * Edit categories for the currently logged in user.
     *
     * @param Request $request
     *
     * @return Response a symfony reponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over categories in the module or
     *                                                                                 if the user is not logged in
     */
    public function edituserAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::category', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        if (!$this->get('zikula_users_module.current_user')->isLoggedIn()) {
            throw new AccessDeniedException($this->__('Error! Editing mode for user-owned categories is only available to users who have logged-in.'));
        }

        $allowUserEdit = $this->getVar('allowusercatedit', 0);
        if (!$allowUserEdit) {
            $this->addFlash('error', $this->__('Error! User-owned category editing has not been enabled. This feature can be enabled by the site administrator.'));

            return $this->responseForErrorMessage();
        }

        $userRoot = $this->getVar('userrootcat', 0);
        if (!$userRoot) {
            $this->addFlash('error', $this->__('Error! Could not determine the user root node.'));

            return $this->responseForErrorMessage();
        }

        $userRootCat = CategoryUtil::getCategoryByPath($userRoot);
        if (!$userRoot) {
            $this->addFlash('error', $this->__f('Error! The user root node seems to point towards an invalid category: %s.', ['%s' => $userRoot]));

            return $this->responseForErrorMessage();
        }

        if ($userRootCat == 1) {
            $this->addFlash('error', $this->__("Error! The root directory cannot be modified in 'user' mode"));

            return $this->responseForErrorMessage();
        }

        $userCatName = $this->getusercategorynameAction();
        if (!$userCatName) {
            $this->addFlash('error', $this->__('Error! Cannot determine user category root node name.'));

            return $this->responseForErrorMessage();
        }

        $thisUserRootCatPath = $userRoot . '/' . $userCatName;
        $thisUserRootCat = CategoryUtil::getCategoryByPath($thisUserRootCatPath);

        $dr = null;
        if (!$thisUserRootCat) {
            $autoCreate = $this->getVar('autocreateusercat', 0);
            if (!$autoCreate) {
                $this->addFlash('error', $this->__('Error! The user root category node for this user does not exist, and the automatic creation flag (autocreate) has not been set.'));

                return $this->responseForErrorMessage();
            }

            $entityManager = $this->get('doctrine.orm.entity_manager');

            $cat = [
                'id' => '',
                'parent' => $entityManager->getReference('ZikulaCategoriesModule:CategoryEntity', $userRootCat['id']),
                'name' => $userCatName,
                'display_name' => [ZLanguage::getLanguageCode() => $userCatName],
                'display_desc' => [ZLanguage::getLanguageCode() => ''],
                'path' => $thisUserRootCatPath,
                'status' => 'A'
            ];

            $obj = new \Zikula\CategoriesModule\Entity\CategoryEntity();
            $obj->merge($cat);
            $entityManager->persist($obj);
            $entityManager->flush();

            // since the original insert can't construct the ipath (since
            // the insert id is not known yet) we update the object here
            $obj->setIPath($userRootCat['ipath'] . '/' . $obj['id']);
            $entityManager->flush();

            $dr = $obj->getID();

            $autoCreateDefaultUserCat = $this->getVar('autocreateuserdefaultcat', 0);
            if ($autoCreateDefaultUserCat) {
                $userdefaultcatname = $this->getVar('userdefaultcatname', $this->__('Default'));
                $cat = [
                    'id' => '',
                    'parent' => $entityManager->getReference('ZikulaCategoriesModule:CategoryEntity', $dr),
                    'is_leaf' => 1,
                    'name' => $userdefaultcatname,
                    'sort_value' => 0,
                    'display_name' => [ZLanguage::getLanguageCode() => $userdefaultcatname],
                    'display_desc' => [ZLanguage::getLanguageCode() => ''],
                    'path' => $thisUserRootCatPath . '/' . $userdefaultcatname,
                    'status' => 'A'
                ];

                $obj2 = new \Zikula\CategoriesModule\Entity\CategoryEntity();
                $obj2->merge($cat);
                $entityManager->persist($obj2);
                $entityManager->flush();

                // since the original insert can't construct the ipath (since
                // the insert id is not known yet) we update the object here
                $obj2->setIPath($obj['ipath'] . '/' . $obj2['id']);
                $entityManager->flush();
            }
        } else {
            $dr = $thisUserRootCat['id'];
        }

        return $this->redirectToRoute('zikulacategoriesmodule_user_edit', ['dr' => $dr]);
    }

    /**
     * @Route("/refer")
     *
     * Refers the user back to the calling page.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function referBackAction(Request $request)
    {
        $referer = $request->getSession()->get('categories_referer');
        $request->getSession()->remove('categories_referer');

        return $this->redirect(System::normalizeUrl($referer));
    }

    /**
     * @Route("/usercategories")
     *
     * Returns the categories for the currently logged in user, really only used for testing purposes.
     *
     * @return array array of categories
     */
    public function getusercategoriesAction()
    {
        return ModUtil::apiFunc('ZikulaCategoriesModule', 'user', 'getusercategories');
    }

    /**
     * @Route("/usercategoryname")
     *
     * Returns the category name for a user, really only used for testing purposes.
     *
     * @return string the username associated with the category
     */
    public function getusercategorynameAction()
    {
        return ModUtil::apiFunc('ZikulaCategoriesModule', 'user', 'getusercategoryname');
    }

    private function responseForErrorMessage()
    {
        return $this->render('@ZikulaCategoriesModule/User/editcategories.html.twig');
    }
}
