<?php

namespace App\Controller;

use App\Entity\CompanyExperience;
use App\Entity\EducatorReviewCompanyExperienceFeedback;
use App\Entity\ProfessionalReviewCompanyExperienceFeedback;
use App\Entity\EducatorReviewTeachLessonExperienceFeedback;
use App\Entity\ProfessionalReviewTeachLessonExperienceFeedback;
use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\Feedback;
use App\Entity\ProfessionalReviewSchoolExperienceFeedback;
use App\Entity\ProfessionalUser;
use App\Entity\SchoolExperience;
use App\Entity\StudentReviewCompanyExperienceFeedback;
use App\Entity\StudentReviewMeetProfessionalExperienceFeedback;
use App\Entity\StudentReviewTeachLessonExperienceFeedback;
use App\Entity\StudentReviewSchoolExperienceFeedback;
use App\Entity\ProfessionalReviewStudentToMeetProfessionalFeedback;
use App\Entity\StudentToMeetProfessionalExperience;
use App\Entity\StudentUser;
use App\Entity\TeachLessonExperience;
use App\Form\EducatorReviewCompanyExperienceFeedbackFormType;
use App\Form\EducatorReviewTeachLessonExperienceFeedbackFormType;
use App\Form\FeedbackType;
use App\Form\FeedbackV2FormType;
use App\Form\Flow\FeedbackFlow;
use App\Form\ProfessionalReviewTeachLessonExperienceFeedbackFormType;
use App\Form\ProfessionalReviewCompanyExperienceFeedbackFormType;
use App\Form\ProfessionalReviewSchoolExperienceFeedbackFormType;
use App\Form\GenericFeedbackFormType;
use App\Form\StudentReviewCompanyExperienceFeedbackFormType;
use App\Form\StudentReviewMeetProfessionalExperienceFeedbackFormType;
use App\Form\StudentReviewTeachLessonExperienceFeedbackFormType;
use App\Form\StudentReviewSchoolExperienceFeedbackFormType;
use App\Form\ProfessionalReviewStudentToMeetProfessionalFeedbackFormType;
use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use App\Util\ServiceHelper;
use Craue\FormFlowBundle\Util\FormFlowUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Ramsey\Uuid\Uuid;

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
     * @Route("/new", name="new", options = { "expose" = true })
     * @param  Request                                  $request
     * @param  \App\Form\Flow\FeedbackFlow              $flow
     * @param  \Craue\FormFlowBundle\Util\FormFlowUtil  $formFlowUtil
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request, FeedbackFlow $flow, FormFlowUtil $formFlowUtil)
    {
        // TODO!!!! Topic satisfaction does not have an experience id right? Do we even need to think about this now? Probably not.
        $experienceId          = $request->query->get('experience_id');
        $type                  = $request->query->get('type');
        $experienceHasFeedback = false;

        $experience = $this->experienceRepository->find($experienceId);

        if (!$experienceId || !$experience = $this->experienceRepository->find($experienceId)) {
            throw new NotFoundHttpException(sprintf("Experience with ID %s not found", $experienceId));
        }

        // TODO !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        // todo should we add the FeedbackFormEntity and the FeedbackFormFieldEntity and the FeedbackSubmissionEntity (fieldId, formId, response) so we can create more flexible forms
        //  in the future?
        // todo if we don't should we take take the fields like awarenessOfCareerOpportunities in EducatorReviewCompanyExperienceFeedback.php
        //  and put it in the base Feedback class? If we do we need a command that pulls all the data out of the child classes and puts it in the base Feedback
        // todo also we will need a console command to data load the feedback responses somehow into the FeedbackSubmissionEntity table right?

        // TODO !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

        // todo 3. Do we need a query parameter for userId|studentId|educatorId? or formType=student|educator, etc?

        //$flow = $this->signUpFlow;

        //$url = $this->generateUrl('sign_up', array_merge(['uuid' => $signUp->getUuid()], $signUp->getQueryParams()));

        //$flow->setGenericFormOptions(['action' => $url]);

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


                $this->entityManager->persist($feedback);
                $this->entityManager->flush();
                $flow->reset();

                $this->addFlash('success', 'Thanks for your feedback!');
            }
        }

        if ($flow->redirectAfterSubmit($submittedForm)) {
            $params = $formFlowUtil->addRouteParameters(array_merge($request->query->all(), $request->attributes->get('_route_params')), $flow);

            return $this->redirect($this->generateUrl($request->attributes->get('_route'), $params));
        }

        /*      $feedback = new Feedback();
              $form     = $this->createForm(FeedbackV2FormType::class, $feedback, [
                  'experience' => $experience,
                  'type'       => $type,
              ]);

              $form->handleRequest($request);

              if ($form->isSubmitted() && $form->isValid()) {
                  $feedback = $form->getData();
              }*/

        return $this->render("feedback/v2/new.html.twig", [
            'form'       => $form->createView(),
            'flow'       => $flow,
            'feedback'   => $feedback,
            'experience' => $experience,
            'experienceHasFeedback' => $experienceHasFeedback
        ]);
    }

}
