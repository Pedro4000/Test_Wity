<?php

namespace WityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

use WityBundle\Entity\Holiday;
use WityBundle\Entity\employee;

class DefaultController extends Controller
{
    public function indexAction($id, Request $request)
    {

    	/*on vient créer ici un formulaire pour enregistrer les demandes de congés, on prend l'entité holiday*/

    	$conge = new Holiday();

        $formBuilder =$this -> get('form.factory')->createBuilder(Formtype::class,$conge);

        $formBuilder
            ->add('start', DateType::class)
            ->add('end', DateType::class)
            ->add('status', TextType::class)
            ->add('comment', TextType::class)
            ->add('employee_id', TextType::class)
            ->add('submit', SubmitType::class);


        $form= $formBuilder->getForm();

        /*si le formulaire de demandé de congé est rempli, on va pouvoir l'enregister en bdd*/

        if ($request->isMethod('POST')) 
        {
            $form->handleRequest($request);


            if ($form->isValid()){

                $em = $this -> getDoctrine()->getManager();

                $em->persist ($conge);
                $em->flush();

                $request->getSession()->getFlashBag()->add('notice', 'Your account has successfully been created');

                /*on retourne vers la meme page en gardant l'id de l'employé*/

                return $this->redirectToRoute('wity_homepage', array('id'=>$id));

            }

        }


        /*ici on vient récuperer les congé de l'employé dont l'id est passé en paramètre
        on fait un findby avec son id pour trouver ses congés, on stocke de résultat dans $currentholidays*/

			$em = $this->getDoctrine()
			->getManager();
			$repo=$em->getRepository('WityBundle:Holiday');

			$currentholidays=$repo->findBy(array('employee_id'=>$id));

		/*ici on vient récuperer les information de l'employé en question pour vérifier son status d'admin ou non*/

			$em = $this->getDoctrine()
			->getManager();
			$repo=$em->getRepository('WityBundle:employee');

			$currentemployee=$repo->findOneBy(array('id'=>$id));

		/*la on vient récuperer les congés en demande de validation pas les admins*/


			$em = $this->getDoctrine()
			->getManager();
			$repo=$em->getRepository('WityBundle:Holiday');

			$pendingholidays=$repo->findBy(array('status'=>0));


		/*et enfin ici la liste de tous les employés*/

			$em = $this->getDoctrine()
			->getManager();
			$repo=$em->getRepository('WityBundle:employee');

			$allemployees=$repo->findAll(array());


        return $this->render('WityBundle:Default:index.html.twig', array(
        	'form'=>$form->createView(),
        	'currentholidays'=>$currentholidays,
        	'employeactuel'=>$currentemployee,
        	'pendingholidays'=>$pendingholidays,
			'allemployees'=>$allemployees));


    }


	public function adminAction($id)
    {

		$em = $this->getDoctrine()
		->getManager();
		$repo=$em->getRepository('WityBundle:Holiday');

		$allvalidholiday=$repo->findby(array('employee_id'=>$id,'status'=>'1'));


		return $this->render('WityBundle:Default:admin.html.twig', array('allvalidholiday'=>$allvalidholiday));

    }
}
