<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Sequence;
use Doctrine\DBAL\Types\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Routing\Annotation\Route as Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Node;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }

    /**
     * @Route("/invite", name="invite")
     *
     *
     */
    public function addInvitationAction(Request $request){
        $newNode = new Node();

        // Leave blank
        $newNode->setIsStaticOrDynDNS(false);
        $newNode->setAddress("");
        $newNode->setPort(0);

        // Version 1
        $newNode->setVersion(1);

        $form = $this->createFormBuilder($newNode)
            ->add('Name', TextType::class)
            //->add('IsStaticOrDynDNS', CheckboxType::class, array('label' => 'Node has static or DynDNS address', 'required' => false))
            //->add('Address', TextType::class, array('label' => 'Static IP or DynDNS address', 'required' => false))
            //->add('Port', TextType::class, array('label' => 'Port tinc is reachable from outside (Port-Forward)', 'required' => false))
            ->add('save', SubmitType::class, array('label' => 'Create Node'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //TODO: If tinc -n [network] invite [name] is successful
            $em = $this->getDoctrine()->getManager();
            $newNode->setDateTimeReg(new \DateTime());
            $em->persist($newNode);
            $em->flush();

            return $this->redirectToRoute('homepage');
        }

        // TODO: Render success page with output and additional information
        return $this->render('default/invite.html.twig',
            array(
                'form' => $form->createView()
            )
        );
    }

    /**
     * @Route("/invite/success", name="invitesuccess")
     */
    public function invitationSuccessAction(){

        /* @var $tinc Process */
        $tinc = $this->get('tinc')->invite('Symfony1339');

        $output = "Oops. Something went wrong.";
        if($tinc) {
            if(!$tinc->getExitCode()){
                $output = $tinc->getOutput();
            }
        }

        return $this->render('default/invitesuccess.html.twig',
            array(
                'output' => $output
            )
        );
    }

    //TODO: Multiple Networks? Then use network[Id] in Select
    /**
     * @Route("/nodes/{seq}", name="nodelist")
     */
    public function listAction($seq){
        return $this->render('default/list.html.twig',
            array(
                'nodes' => $this->getDoctrine()->getManager()->getRepository('AppBundle:Node')->findBySeqGreaterThan($seq)
            )
        );
    }
}
