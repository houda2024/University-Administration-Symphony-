<?php

namespace App\Controller;

use App\Entity\Candidature;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Job;
use DateTime;
use App\Entity\Image;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class JobController extends AbstractController
{
    /**
     * @Route("/job", name="app_job")
     */
    public function index(): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $job = new Job();
        $job->setType("Architecte");
        $job->setCompany("Biksh Holding");
        $job->setDescription("Genie logiciel");
        $job->setExpiresAt(new DateTime());
        $job->setEmail("biksh@outlook.fr");
        $manager->persist($job);
        $image = new Image();
        $image->setUrl("https://www.ecoles.com.tn/sites/default/files/universite/logo/polytechnique-sousse-logo.jpg");
        $image->setAlt("Ecole Poly");
        $manager->persist($image);
        $job->setImage($image);
        $manager->flush();


        return $this->render('job/index.html.twig', [
            'id' => $job->getId(),
        ]);
    }

    /**
     * @Route("/job/{id}", name="job_show")
     */
    public function show($id)
    {
        $job = $this->getDoctrine()
            ->getRepository(Job::class)
            ->find($id);
        if (!$job) {
            throw $this->createNotFoundException(
                'No job found for id ' . $id
            );
        }
        return $this->render(
            'job/show.html.twig',
            [
                'job' => $job
            ]
        );
    }
    /**
     * @Route("/Ajouter", name="cand_add")
     */
    public function Ajouter(Request $request)
    {
        $candidat = new Candidature();
        $fb = $this->createFormBuilder($candidat)
            ->add("candidat", TextType::class)
            ->add('contenu', TextType::class, array("label" => "Contenu"))
            ->add("datec", DateType::class, array("label" => "Date"))
            ->add("job", EntityType::class, [
                "class" => Job::class,
                "choice_label" => "type"
            ])
            ->add("Valider", SubmitType::class);
        $form = $fb->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($candidat);
            $em->flush();
        }
        return $this->render("job/ajouter.html.twig", ["f" => $form->createView(), "type" => "cand"]);
    }
    /**
     * @Route("/Add", name="job_add")
     */
    public function Ajouter1(Request $request)
    {
        # code...
        $job = new Job();
        $form = $this->createForm("App\Form\JobType", $job);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($job);
            $em->flush();
            $seesion = new Session();
            $seesion->getFlashBag()->add("notice", "Candidat bien ajouté");

            return $this->redirectToRoute("home");
        }
        return $this->render("job/ajouter.html.twig", ["f" => $form->createView(), "type" => "job"]);
    }
    /** 
     * @Route("/",name="home")
     */
    public function home()
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Candidature::class);
        $lesCandidats = $repo->findAll();
        return $this->render("job/home.html.twig", ["cands" => $lesCandidats]);
    }

    /** 
     * @Route("/suppCand/{id}",name="cand_delete")
     */
    public function DeleteCandidat(Request $req, $id): Response
    {
        # code...
        $c = $this->getDoctrine()->getRepository(Candidature::class)->find($id);
        if (!$c) {
            throw $this->createNotFoundException("Non job found for id " . $id);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($c);
        $entityManager->flush();
        $seesion = new Session();
        $seesion->getFlashBag()->add("notice", "Candidat bien supprimé");

        return $this->redirectToRoute("home");
    }

    /**
     * @Route("/editU/{id}", name="edit_user")
     * Method({"GET","POST"})
     */
    public function edit(Request $request, $id)
    {
        $candidat = new Candidature();
        $candidat = $this->getDoctrine()
            ->getRepository(Candidature::class)
            ->find($id);
        if (!$candidat) {
            throw $this->createNotFoundException(
                'No candidat found for id ' . $id
            );
        }
        $fb = $this->createFormBuilder($candidat)
            ->add('candidat', TextType::class)
            ->add('contenu', TextType::class, array("label" => "Contenu"))
            ->add('datec', DateType::class)
            ->add('job', EntityType::class, [
                'class' => Job::class,
                'choice_label' => 'type',
            ])
            ->add('Valider', SubmitType::class);
        $form = $fb->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render(
            'job/ajouter.html.twig',
            ['f' => $form->createView(),"type"=>"editUser"]
        );
    }
}
