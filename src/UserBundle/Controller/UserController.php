<?php

namespace UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UserBundle\Entity\Address;
use UserBundle\Entity\User;
use UserBundle\Form\UserType;
use UserBundle\Service\UserManager;

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
     *
     * @param UserManager $userManager
     * @return Response
     * @throws \LogicException
     */
    public function indexAction(UserManager $userManager)
    {
        $users = $userManager->getPaginatedUsers();

        $deleteForms = [];

        /** @var User[] $users */
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
     *
     * @param Request $request
     * @param UserManager $userManager
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request, UserManager $userManager)
    {
        $user = new User();

        $address = new Address();
        $address->setIsBilling(false);

        $user->getAddresses()->add($address);
        $roleChoices = $user->getRoleChoices();

        $form = $this->createForm(UserType::class, $user, ['role_choices' => $roleChoices]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->createUser($user);

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
     *
     * @param User $user
     * @return Response
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
     *
     * @param Request $request
     * @param User $user
     * @param UserManager $userManager
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, User $user, UserManager $userManager)
    {
        $deleteForm = $this->createDeleteForm($user);

        $roleChoices = $user->getRoleChoices();

        $editForm = $this->createForm(UserType::class, $user, ['password_required' => false, 'role_choices' => $roleChoices]);

        $user->setOriginalPassword($user->getPassword());

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $userManager->updateUser($user);

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
     *
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     * @throws \InvalidArgumentException
     * @throws \LogicException
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
        return $this->createFormBuilder(
            null,
            [
                'attr' => [
                    'class' => 'delete',
                    'data-confirm' => $this->get('translator')->trans('COMMON_DELETE_CONFIRM', [], 'common')
                ]
            ])
            ->setAction($this->generateUrl('backend_user_delete', ['id' => $user->getId()]))
            ->setMethod('DELETE')
            ->getForm();
    }
}
