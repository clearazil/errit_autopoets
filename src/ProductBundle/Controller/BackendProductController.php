<?php

namespace ProductBundle\Controller;

use ProductBundle\Entity\Product;
use ProductBundle\Entity\ProductImage as Image;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Product controller.
 *
 * @Route("backend/products")
 */
class BackendProductController extends Controller
{
    /**
     * Lists all product entities.
     *
     * @Route("/", name="backend_product_index")
     * @Method("GET")
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT product FROM ProductBundle:Product product";
        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        $deleteForms = [];

        foreach ($pagination as $product) {
            $deleteForms[$product->getId()] = $this->createDeleteForm($product)->createView();
        }

        return $this->render('ProductBundle:Product:Backend/index.html.twig', [
            'pagination' => $pagination,
            'deleteForms' => $deleteForms,
        ]);
    }

    /**
     * Creates a new product entity.
     *
     * @Route("/new", name="backend_product_new")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request)
    {
        $product = new Product();
        $form = $this->createForm('ProductBundle\Form\ProductType', $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('backend_product_show', array('id' => $product->getId()));
        }

        return $this->render('ProductBundle:Product:Backend/new.html.twig', array(
            'product' => $product,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a new product image entity.
     *
     * @Route("/image/new/{productId}", name="backend_product_image_new")
     * @ParamConverter("product", options={"mapping": {"productId"   : "id"}})
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @param Product $product
     * @return RedirectResponse|Response
     */
    public function newProductImageAction(Request $request, Product $product)
    {
        $image = new Image;
        $form = $this->createForm('ProductBundle\Form\ProductImageType', $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $file stores the uploaded Image file
            /** @var UploadedFile $file */
            $file = $image->getImage();

            // Generate a unique name for the file before saving it
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            $imagesDirectory = $this->getParameter('product_images_directory');
            // Move the file to the directory where product images are stored
            $file->move(
                $imagesDirectory,
                $fileName
            );

            $this->get('image.handling')->open($imagesDirectory . '/' . $fileName)
                ->forceResize(2000, 1300)
                ->save($imagesDirectory . '/' . $fileName);

            $this->get('image.handling')->open($imagesDirectory . '/' . $fileName)
                ->resize('50%')
                ->save($imagesDirectory . '/thumbnail/' . $fileName);

            // Update the 'image' property to store the image file name
            // instead of its contents
            $image->setImage($fileName);

            $image->setProduct($product);

            $em = $this->getDoctrine()->getManager();
            $em->persist($image);
            $em->flush();

            return $this->redirectToRoute('backend_product_show', array('id' => $product->getId()));
        }

        return $this->render('ProductBundle:Product:Backend/new_image.html.twig', array(
            'image' => $image,
            'product' => $product,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a product entity.
     *
     * @Route("/{id}", name="backend_product_show")
     * @Method("GET")
     *
     * @param Product $product
     * @return Response
     */
    public function showAction(Product $product)
    {
        $deleteForm = $this->createDeleteForm($product);

        $imageDeleteForms = [];

        foreach ($product->getImages() as $image) {
            $imageDeleteForms[$image->getId()] = $imageDeleteForm = $this->createDeleteImageForm($image)->createView();
        }

        return $this->render('ProductBundle:Product:Backend/show.html.twig', array(
            'product' => $product,
            'imageDeleteForms' => $imageDeleteForms,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing product entity.
     *
     * @Route("/{id}/edit", name="backend_product_edit")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @param Product $product
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, Product $product)
    {
        $editForm = $this->createForm('ProductBundle\Form\ProductType', $product);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('backend_product_edit', array('id' => $product->getId()));
        }

        return $this->render('ProductBundle:Product:Backend/edit.html.twig', array(
            'product' => $product,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a product entity.
     *
     * @Route("/{id}", name="backend_product_delete")
     * @Method("DELETE")
     *
     * @param Request $request
     * @param Product $product
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Product $product)
    {
        $form = $this->createDeleteForm($product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();
        }

        return $this->redirectToRoute('backend_product_index');
    }

    /**
     * Deletes a product image entity.
     *
     * @Route("/image/delete/{id}", name="backend_product_image_delete")
     * @Method("DELETE")
     *
     * @param Request $request
     * @param Image $image
     * @return RedirectResponse
     */
    public function deleteImageAction(Request $request, Image $image)
    {
        $productId = $image->getProduct()->getId();

        $form = $this->createDeleteImageForm($image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO delete image and thumbnail

            $em = $this->getDoctrine()->getManager();
            $em->remove($image);
            $em->flush();
        }

        return $this->redirectToRoute('backend_product_show', ['id' => $productId]);
    }

    /**
     * Creates a form to delete a product entity.
     *
     * @param Image $image
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createDeleteImageForm(Image $image)
    {
        return $this->createFormBuilder(null, ['attr' => ['class' => 'delete', 'data-confirm' => $this->get('translator')->trans('COMMON_DELETE_CONFIRM', [], 'common')]])
            ->setAction($this->generateUrl('backend_product_image_delete', array('id' => $image->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }

    /**
     * Creates a form to delete a product entity.
     *
     * @param Product $product The product entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Product $product)
    {
        return $this->createFormBuilder(null, ['attr' => ['class' => 'delete', 'data-confirm' => $this->get('translator')->trans('COMMON_DELETE_CONFIRM', [], 'common')]])
            ->setAction($this->generateUrl('backend_product_delete', ['id' => $product->getId()]))
            ->setMethod('DELETE')
            ->getForm();
    }
}
