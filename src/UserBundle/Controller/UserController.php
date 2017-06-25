<?php

namespace UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\Address;
use UserBundle\Entity\User;

/**
 * User controller.
 *
 * @Route("backend/user")
 */
class UserController extends Controller
{
    /**
     * Lists all user entities.
     *
     * @Route("/", name="backend_user_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $dql = 'SELECT user FROM UserBundle:User user';
        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $users = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/,
            ['defaultSortFieldName' => 'user.created_at', 'defaultSortDirection' => 'desc']
        );

        $deleteForms = [];

        foreach ($users as $user) {
            $deleteForms[$user->getId()] = $this->createDeleteForm($user)->createView();
        }

        return $this->render('UserBundle:User:index.html.twig', [
            'users' => $users,
            'deleteForms' => $deleteForms,
        ]);
    }

    /**
     * Creates a new user entity.
     *
     * @Route("/new", name="backend_user_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $user = new User();

        $address = new Address();
        $address->setIsBilling(false);

        $user->getAddresses()->add($address);
        $roleChoices = $user->getRoleChoices();

        $form = $this->createForm('UserBundle\Form\UserType', $user, ['role_choices' => $roleChoices]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $encoder = $this->container->get('security.password_encoder');
            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);

            foreach ($user->getUserRoles() as $role) {
                $role->setUser($user);
                $em->persist($role);
            }

            $address->setUser($user);
            $em->persist($address);

            $em->flush();

            return $this->redirectToRoute('backend_user_show', array('id' => $user->getId()));
        }

        return $this->render('UserBundle:User:new.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a user entity.
     *
     * @Route("/{id}", name="backend_user_show")
     * @Method("GET")
     */
    public function showAction(User $user)
    {
        $deleteForm = $this->createDeleteForm($user);

        return $this->render('UserBundle:User:show.html.twig', array(
            'user' => $user,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing user entity.
     *
     * @Route("/{id}/edit", name="backend_user_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, User $user)
    {
        $deleteForm = $this->createDeleteForm($user);

        $address = $user->getAddress();

        $roleChoices = $user->getRoleChoices();

        $editForm = $this->createForm('UserBundle\Form\UserType', $user, ['password_required' => false, 'role_choices' => $roleChoices]);

        $originalPassword = $user->getPassword();

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            if (empty($user->getPassword())) {
                $user->setPassword($originalPassword);
            } else {
                $encoder = $this->container->get('security.password_encoder');
                $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);

            foreach ($user->getUserRoles() as $role) {
                $role->setUser($user);
                $em->persist($role);
            }

            $address->setUser($user);
            $em->persist($address);
            $em->flush();

            return $this->redirectToRoute('backend_user_show', array('id' => $user->getId()));
        }

        return $this->render('UserBundle:User:edit.html.twig', array(
            'user' => $user,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a user entity.
     *
     * @Route("/{id}", name="backend_user_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, User $user)
    {
        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('backend_user_index');
    }

    /**
     * Creates a form to delete a user entity.
     *
     * @param User $user The user entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(User $user)
    {
        return $this->createFormBuilder(null, ['attr' => ['class' => 'delete', 'data-confirm' => $this->get('translator')->trans('COMMON_DELETE_CONFIRM', [], 'common')]])
            ->setAction($this->generateUrl('backend_user_delete', ['id' => $user->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
