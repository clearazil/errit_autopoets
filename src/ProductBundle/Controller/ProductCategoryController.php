<?php

namespace ProductBundle\Controller;

use ProductBundle\Entity\ProductCategory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Productcategory controller.
 *
 * @Route("backend/products/categories")
 */
class ProductCategoryController extends Controller
{
    /**
     * Lists all productCategory entities.
     *
     * @Route("/", name="productcategory_index")
     * @Method("GET")
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $dql   = "SELECT productCategory FROM ProductBundle:ProductCategory productCategory";
        $query = $em->createQuery($dql);

        $paginator  = $this->get('knp_paginator');
        $productCategories = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        $deleteForms = [];

        foreach ($productCategories as $productCategory) {
            $deleteForms[$productCategory->getId()] = $this->createDeleteForm($productCategory)->createView();
        }

        return $this->render('ProductBundle:Product:Category/index.html.twig', [
            'productCategories' => $productCategories,
            'deleteForms' => $deleteForms,
        ]);
    }

    /**
     * Creates a new productCategory entity.
     *
     * @Route("/new", name="productcategory_new")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request)
    {
        $productCategory = new Productcategory();
        $form = $this->createForm('ProductBundle\Form\ProductCategoryType', $productCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($productCategory);
            $em->flush();

            return $this->redirectToRoute('productcategory_show', array('id' => $productCategory->getId()));
        }

        return $this->render('ProductBundle:Product:Category/new.html.twig', array(
            'productCategory' => $productCategory,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a productCategory entity.
     *
     * @Route("/{id}", name="productcategory_show")
     * @Method("GET")
     *
     * @param ProductCategory $productCategory
     * @return Response
     */
    public function showAction(ProductCategory $productCategory)
    {
        $deleteForm = $this->createDeleteForm($productCategory);

        return $this->render('ProductBundle:Product:Category/show.html.twig', array(
            'productCategory' => $productCategory,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing productCategory entity.
     *
     * @Route("/{id}/edit", name="productcategory_edit")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @param ProductCategory $productCategory
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, ProductCategory $productCategory)
    {
        $deleteForm = $this->createDeleteForm($productCategory);
        $editForm = $this->createForm('ProductBundle\Form\ProductCategoryType', $productCategory);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('productcategory_edit', array('id' => $productCategory->getId()));
        }

        return $this->render('ProductBundle:Product:Category/edit.html.twig', array(
            'productCategory' => $productCategory,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a productCategory entity.
     *
     * @Route("/{id}", name="productcategory_delete")
     * @Method("DELETE")
     *
     * @param Request $request
     * @param ProductCategory $productCategory
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, ProductCategory $productCategory)
    {
        $form = $this->createDeleteForm($productCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($productCategory);
            $em->flush();
        }

        return $this->redirectToRoute('productcategory_index');
    }

    /**
     * Creates a form to delete a productCategory entity.
     *
     * @param ProductCategory $productCategory The productCategory entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ProductCategory $productCategory)
    {
        return $this->createFormBuilder(null, ['attr' => ['class' => 'delete', 'data-confirm' => $this->get('translator')->trans('COMMON_DELETE_CONFIRM', [], 'common')]])
            ->setAction($this->generateUrl('productcategory_delete', ['id' => $productCategory->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
