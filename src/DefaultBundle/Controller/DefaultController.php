<?php

namespace DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home"))
     *
     * @return Response
     */
    public function indexAction()
    {
        $amount = 4;

        //Retrieve the EntityManager first
        $em = $this->getDoctrine()->getManager();

        //Get the number of rows from your table
        $rows = $em->createQuery('SELECT COUNT(product.id) FROM ProductBundle:Product product')->getSingleScalarResult();

        $offset = max(0, rand(0, $rows - $amount - 1));

        //Get the first $amount users starting from a random point
        $query = $em->createQuery('
                        SELECT DISTINCT product
                        FROM ProductBundle:Product product')
            ->setMaxResults($amount)
            ->setFirstResult($offset);

        $result = $query->getResult();

        return $this->render('DefaultBundle:Default:index.html.twig', [
            'products' => $result,
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
