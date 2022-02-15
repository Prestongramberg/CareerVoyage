<?php

namespace App\Controller;

use App\Entity\Experience;
use App\Entity\Feedback;
use App\Entity\User;
use App\Form\Flow\FeedbackFlow;
use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use App\Util\ServiceHelper;
use Craue\FormFlowBundle\Util\FormFlowUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FeedbackV2Controller
 *
 * @package App\Controller
 * @Route("/feedback", name="feedback_v2_")
 */
class FeedbackV2Controller extends AbstractController
{

    use FileHelper;
    use RandomStringGenerator;
    use ServiceHelper;


    /**
     * @Route("/{uuid}/new", name="new", options = { "expose" = true })
     * @param  \App\Entity\Experience                   $experience
     * @param  Request                                  $request
     * @param  \App\Form\Flow\FeedbackFlow              $flow
     * @param  \Craue\FormFlowBundle\Util\FormFlowUtil  $formFlowUtil
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Experience $experience, Request $request, FeedbackFlow $flow, FormFlowUtil $formFlowUtil)
    {
        $loggedInuser = $this->getUser();
        // TODO!!!! Topic satisfaction does not have an experience id right? Do we even need to think about this now? Probably not.
        $experienceHasFeedback = false;

        $feedback = new Feedback();
        $feedback->setExperience($experience);

        $flow->bind($feedback);

        $form = $submittedForm = $flow->createForm();

        if ($flow->isValid($submittedForm)) {
            $flow->saveCurrentStepData($submittedForm);

            if ($flow->nextStep()) {
                // form for the next step
                $form = $flow->createForm();
            } else {
                // todo do all the normalizing of the feedback that the feedback normalizer command does??

                $feedbackUser = null;

                if($email = $feedback->getEmail()) {
                    $feedbackUser = $this->userRepository->findOneBy([
                        'email' => $email
                    ]);
                }

                if($loggedInuser instanceof User) {
                    $feedbackUser = $loggedInuser;
                }

                $feedback->setUser($feedbackUser);

                $this->entityManager->persist($feedback);
                $this->entityManager->flush();
                $flow->reset();

                return $this->redirectToRoute('feedback_v2_thanks');
            }
        }

        $params = $formFlowUtil->addRouteParameters(array_merge($request->query->all(), $request->attributes->get('_route_params')), $flow);
        $url    = $this->generateUrl($request->attributes->get('_route'), $params);

        if ($flow->redirectAfterSubmit($submittedForm)) {
            $params = $formFlowUtil->addRouteParameters(array_merge($request->query->all(), $request->attributes->get('_route_params')), $flow);

            return $this->redirect($this->generateUrl($request->attributes->get('_route'), $params));
        }

        if ($request->request->has('changeableField')) {
            return new JsonResponse(
                [
                    'success'    => false,
                    'formMarkup' => $this->renderView("feedback/v2/new.html.twig", [
                        'form'                  => $form->createView(),
                        'flow'                  => $flow,
                        'route'                 => $url,
                        'feedback'              => $feedback,
                        'experience'            => $experience,
                        'experienceHasFeedback' => $experienceHasFeedback,
                    ]),
                ], Response::HTTP_BAD_REQUEST
            );
        }

        return $this->render("feedback/v2/new.html.twig", [
            'form'                  => $form->createView(),
            'flow'                  => $flow,
            'route'                 => $url,
            'feedback'              => $feedback,
            'experience'            => $experience,
            'experienceHasFeedback' => $experienceHasFeedback,
        ]);
    }

    /**
     * @Route("/thanks", name="thanks", options = { "expose" = true })
     * @param  Request                                  $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function thanksAction(Request $request)
    {
        $loggedInuser = $this->getUser();
        // TODO!!!! Topic satisfaction does not have an experience id right? Do we even need to think about this now? Probably not.
     /*   $experienceId          = $request->query->get('experience_id');
        $experienceHasFeedback = false;

        if (!$experienceId || !$experience = $this->experienceRepository->find($experienceId)) {
            throw new NotFoundHttpException(sprintf("Experience with ID %s not found", $experienceId));
        }*/

        return $this->render("feedback/v2/thanks.html.twig", [
            //'experience'            => $experience,
            //'experienceHasFeedback' => $experienceHasFeedback,
        ]);
    }

}
