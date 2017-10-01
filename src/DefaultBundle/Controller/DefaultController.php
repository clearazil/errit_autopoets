<?php

namespace DefaultBundle\Controller;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home"))
     *
     * @return Response
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->getRepository('ProductBundle:Product')
            ->randomProductsQuery(4);

        return $this->render('DefaultBundle:Default:index.html.twig', [
            'products' => $query->getResult(),
        ]);
    }

    /**
     * @Route("/backend", name="backend"))
     *
     * @return RedirectResponse
     */
    public function backendAction()
    {
        return $this->redirectToRoute('backend_product_index');
    }
}
