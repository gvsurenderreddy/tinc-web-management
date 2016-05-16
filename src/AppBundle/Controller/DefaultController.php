<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Sequence;
use AppBundle\Service\TincService;
use Doctrine\DBAL\Types\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Node;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->redirectToRoute("invite");
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
        $newNode->setIsActive(false);

        // Version 1
        $newNode->setVersion(1);

        $form = $this->createFormBuilder($newNode)
            ->add('Name', TextType::class)
            ->add('IsStaticOrDynDNS', CheckboxType::class, array('label' => 'Node has static or DynDNS address', 'required' => false))
            ->add('Address', TextType::class, array('label' => 'Static IP or DynDNS address', 'required' => false))
            ->add('Port', TextType::class, array('label' => 'Port tinc is reachable from outside (Port-Forward)', 'required' => false, 'data' => 655))
            ->add('save', SubmitType::class, array('label' => 'Create Node'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /* @var $tinc TincService */
            $tinc = $this->get('tinc');
            $output = "";

            try {
                /* @var $tincInvite Process */
                $tincInvite = $tinc->invite($newNode->getName());

                if($tincInvite) {
                    if(!$tincInvite->getExitCode()){
                        $output = $tincInvite->getOutput();

                        $newNode->setInvitationCode($output);

                        $doctrine = $this->getDoctrine();

                        $em = $doctrine->getManager();
                        $newNode->setDateTimeReg(new \DateTime());
                        $em->persist($newNode);
                        $em->flush();

                        $repository = $doctrine->getRepository('AppBundle:Node');
                        $query = $repository->createQueryBuilder('n')
                            ->select('n.name')
                            ->where('n.isStaticOrDynDNS = :state')
                            ->setParameter('state', true)
                            ->getQuery();
                        $arConnectToNodes = $query->getArrayResult();

                        // Write newNode's address details to invitation file
                        $tinc->writeConnectToNodes(
                            $newNode->getName(),
                            $arConnectToNodes
                        );
                    }
                }

            } catch (ProcessFailedException $e){
                //TODO: Log to DB

                return $this->render('default/inviteerror.html.twig', array('output' => $e));
            }

            return $this->render('default/invitesuccess.html.twig',
                array(
                    'code' => $output
                )
            );
        }

        return $this->render('default/invite.html.twig',
            array(
                'form' => $form->createView()
            )
        );
    }

    //TODO: Multiple Networks? Then use network[Id] in Select
    /**
     * @Route("/nodes/{seq}", name="nodelist")
     */
    public function listAction($seq){

        $result = array();
        $result['nodes'] = $this->getDoctrine()->getManager()->getRepository('AppBundle:Node')->findNameBySeqGreaterThan($seq);
        $result['seq'] = $this->getDoctrine()->getManager()->getRepository('AppBundle:Sequence')->getLastSeq('sync');

        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/node/{nodeName}", name="download_hostfile")
     *
     */
    public function getHostAction($nodeName){
        $file = $this->get('tinc')->getHostFileName($nodeName);
        if(file_exists($file)){
            $response = new BinaryFileResponse($file);
            return $response;
        } else {
            return new Response('', 404);
        }
    }
}
