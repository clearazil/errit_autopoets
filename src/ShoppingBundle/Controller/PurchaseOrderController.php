<?php

namespace ShoppingBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ShoppingBundle\Entity\PurchaseOrder;
use ShoppingBundle\Form\PurchaseOrderType;
use ShoppingBundle\Service\PurchaseManager;
use ShoppingBundle\Service\PurchaseOrderCreator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Purchaseorder controller.
 *
 * @Route("backend/order")
 */
class PurchaseOrderController extends Controller
{
    /**
     * Lists all purchaseOrder entities.
     *
     * @Route("/", name="order_index")
     * @Method("GET")
     *
     * @param PurchaseManager $purchaseManager
     * @return Response
     */
    public function indexAction(PurchaseManager $purchaseManager)
    {
        $purchaseOrders = $purchaseManager->getPaginatedPurchaseOrders();

        $deleteForms = [];

        foreach ($purchaseOrders as $purchaseOrder) {
            $deleteForms[$purchaseOrder->getId()] = $this->createDeleteForm($purchaseOrder)->createView();
        }

        return $this->render('ShoppingBundle:PurchaseOrder:index.html.twig', [
            'purchaseOrders' => $purchaseOrders,
            'deleteForms' => $deleteForms,
        ]);
    }

    /**
     * Creates a new purchaseOrder entity.
     *
     * @Route("/new", name="order_new")
     * @Method({"GET", "POST"})
     *
     * @param PurchaseOrderCreator $purchaseOrderCreator
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function newAction(PurchaseOrderCreator $purchaseOrderCreator, Request $request)
    {
        $purchaseOrder = $purchaseOrderCreator->getNewPurchaseOrder();

        $form = $this->createForm(PurchaseOrderType::class, $purchaseOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $purchaseOrder = $purchaseOrderCreator->createNewPurchaseOrder($purchaseOrder);

            return $this->redirectToRoute('order_show', array('id' => $purchaseOrder->getId()));
        }

        return $this->render('ShoppingBundle:PurchaseOrder:new.html.twig', array(
            'purchaseOrder' => $purchaseOrder,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a purchaseOrder entity.
     *
     * @Route("/{id}", name="order_show")
     * @Method("GET")
     *
     * @param PurchaseOrder $purchaseOrder
     * @return Response
     */
    public function showAction(PurchaseOrder $purchaseOrder)
    {
        $deleteForm = $this->createDeleteForm($purchaseOrder);

        return $this->render('ShoppingBundle:PurchaseOrder:show.html.twig', array(
            'purchaseOrder' => $purchaseOrder,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing purchaseOrder entity.
     *
     * @Route("/{id}/edit", name="order_edit")
     * @Method({"GET", "POST"})
     *
     * @param PurchaseOrderCreator $purchaseOrderCreator
     * @param Request $request
     * @param PurchaseOrder $purchaseOrder
     * @return RedirectResponse|Response
     */
    public function editAction(PurchaseOrderCreator $purchaseOrderCreator, Request $request, PurchaseOrder $purchaseOrder)
    {
        $deleteForm = $this->createDeleteForm($purchaseOrder);

        $originalOrderLines = $purchaseOrderCreator->getOriginalOrderLines($purchaseOrder);

        $editForm = $this->createForm(PurchaseOrderType::class, $purchaseOrder);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $purchaseOrder = $purchaseOrderCreator->updatePurchaseOrder($purchaseOrder, $originalOrderLines);

            return $this->redirectToRoute('order_edit', array('id' => $purchaseOrder->getId()));
        }

        return $this->render('ShoppingBundle:PurchaseOrder:edit.html.twig', array(
            'purchaseOrder' => $purchaseOrder,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a purchaseOrder entity.
     *
     * @Route("/{id}", name="order_delete")
     * @Method("DELETE")
     *
     * @param Request $request
     * @param PurchaseOrder $purchaseOrder
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, PurchaseOrder $purchaseOrder)
    {
        $form = $this->createDeleteForm($purchaseOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($purchaseOrder);
            $em->flush();
        }

        return $this->redirectToRoute('order_index');
    }

    /**
     * Creates a form to delete a purchaseOrder entity.
     *
     * @param PurchaseOrder $purchaseOrder The purchaseOrder entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(PurchaseOrder $purchaseOrder)
    {
        return $this->createFormBuilder(null, ['attr' => ['class' => 'delete', 'data-confirm' => $this->get('translator')->trans('COMMON_DELETE_CONFIRM', [], 'common')]])
            ->setAction($this->generateUrl('order_delete', ['id' => $purchaseOrder->getId()]))
            ->setMethod('DELETE')
            ->getForm();
    }
}
