<?php

namespace ShoppingBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ShoppingBundle\Entity\PurchaseOrder;
use ShoppingBundle\Entity\PurchaseOrderLine;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\Address;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT purchaseOrder FROM ShoppingBundle:PurchaseOrder purchaseOrder";
        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');

        /** @var PurchaseOrder[] $purchaseOrders */
        $purchaseOrders = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

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
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request)
    {
        $purchaseOrder = new Purchaseorder();

        $address = new Address();
        $address->setIsBilling(false);

        $purchaseOrderLine = new PurchaseOrderLine;
        $purchaseOrder->addPurchaseOrderLine($purchaseOrderLine);

        $purchaseOrder->getAddresses()->add($address);

        $form = $this->createForm('ShoppingBundle\Form\PurchaseOrderType', $purchaseOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($purchaseOrder);

            foreach ($purchaseOrder->getPurchaseOrderLines() as $purchaseOrderLine) {
                $purchaseOrderLine->setPurchaseOrder($purchaseOrder);
                $em->persist($purchaseOrderLine);
            }

            $em->persist($address);
            $em->flush();

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
     * @param Request $request
     * @param PurchaseOrder $purchaseOrder
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, PurchaseOrder $purchaseOrder)
    {
        $deleteForm = $this->createDeleteForm($purchaseOrder);

        $originalOrderLines = new ArrayCollection();

        // Create an ArrayCollection of the current PurchaseOrderLine objects in the database
        foreach ($purchaseOrder->getPurchaseOrderLines() as $purchaseOrderLine) {
            $originalOrderLines->add($purchaseOrderLine);
        }

        $editForm = $this->createForm('ShoppingBundle\Form\PurchaseOrderType', $purchaseOrder);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // remove purchaseOrderLines if they are deleted
            foreach ($originalOrderLines as $purchaseOrderLine) {
                if ($purchaseOrder->getPurchaseOrderLines()->contains($purchaseOrderLine) === false) {
                    $purchaseOrder->getPurchaseOrderLines()->removeElement($purchaseOrderLine);

                    $em->remove($purchaseOrderLine);
                }
            }

            foreach ($purchaseOrder->getPurchaseOrderLines() as $purchaseOrderLine) {
                $purchaseOrderLine->setPurchaseOrder($purchaseOrder);
                $em->persist($purchaseOrderLine);
            }

            $em->flush();

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
