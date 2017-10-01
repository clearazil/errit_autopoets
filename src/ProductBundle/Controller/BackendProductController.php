<?php

namespace ProductBundle\Controller;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use ProductBundle\Entity\Product;
use ProductBundle\Entity\ProductImage as Image;
use ProductBundle\Form\ProductImageType;
use ProductBundle\Form\ProductType;
use ProductBundle\Service\ProductManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Translation\Exception\InvalidArgumentException;

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
     * @param ProductManager $productManager
     * @param Request $request
     * @return Response
     * @throws InvalidOptionsException
     * @throws InvalidParameterException
     * @throws MissingMandatoryParametersException
     * @throws RouteNotFoundException
     * @throws InvalidArgumentException
     * @throws \LogicException
     */
    public function indexAction(ProductManager $productManager, Request $request)
    {
        $products = $productManager->getPaginatedProducts($request);

        $deleteForms = $productManager->getDeleteForms($products);

        return $this->render('ProductBundle:Product:Backend/index.html.twig', [
            'pagination' => $products,
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
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function newAction(Request $request)
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
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
     * @param ProductManager $productManager
     * @return RedirectResponse|Response
     * @throws OptimisticLockException
     * @throws ORMInvalidArgumentException
     * @throws FileException
     */
    public function newProductImageAction(Request $request, Product $product, ProductManager $productManager)
    {
        $image = new Image;
        $form = $this->createForm(ProductImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $productManager->createProductImage($image, $product, $this->getParameter('product_images_directory'));

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
     * @param ProductManager $productManager
     * @return Response
     * @throws InvalidOptionsException
     * @throws InvalidParameterException
     * @throws MissingMandatoryParametersException
     * @throws RouteNotFoundException
     * @throws InvalidArgumentException
     */
    public function showAction(Product $product, ProductManager $productManager)
    {
        $deleteForm = $productManager->createDeleteForm($product);

        $imageDeleteForms = [];

        foreach ($product->getImages() as $image) {
            $imageDeleteForms[$image->getId()] = $imageDeleteForm = $productManager->createDeleteImageForm($image)->createView();
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
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function editAction(Request $request, Product $product)
    {
        $editForm = $this->createForm(ProductType::class, $product);
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
     * @param ProductManager $productManager
     * @param Product $product
     * @return RedirectResponse
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function deleteAction(Request $request, Product $product, ProductManager $productManager)
    {
        $form = $productManager->createDeleteForm($product);
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
     * @param ProductManager $productManager
     * @return RedirectResponse
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function deleteImageAction(Request $request, Image $image, ProductManager $productManager)
    {
        $productId = $image->getProduct()->getId();

        $form = $productManager->createDeleteImageForm($image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO delete image and thumbnail

            $em = $this->getDoctrine()->getManager();
            $em->remove($image);
            $em->flush();
        }

        return $this->redirectToRoute('backend_product_show', ['id' => $productId]);
    }
}
