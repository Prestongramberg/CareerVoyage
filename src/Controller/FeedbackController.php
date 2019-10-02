<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyExperience;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\EducatorReviewCompanyExperienceFeedback;
use App\Entity\EducatorReviewTeachLessonExperienceFeedback;
use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\Feedback;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\RegionalCoordinatorRequest;
use App\Entity\SecondaryIndustry;
use App\Entity\StateCoordinator;
use App\Entity\StateCoordinatorRequest;
use App\Entity\StudentReviewCompanyExperienceFeedback;
use App\Entity\StudentReviewTeachLessonExperienceFeedback;
use App\Entity\StudentUser;
use App\Entity\TeachLessonExperience;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\EducatorReviewCompanyExperienceFeedbackFormType;
use App\Form\EducatorReviewTeachLessonExperienceFeedbackFormType;
use App\Form\NewCompanyFormType;
use App\Form\NewLessonType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\RegionalCoordinatorFormType;
use App\Form\StateCoordinatorFormType;
use App\Form\StudentReviewCompanyExperienceFeedbackFormType;
use App\Form\StudentReviewTeachLessonExperienceFeedbackFormType;
use App\Mailer\RequestsMailer;
use App\Mailer\SecurityMailer;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\StateCoordinatorRepository;
use App\Repository\StateCoordinatorRequestRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Asset\Packages;

/**
 * Class FeedbackController
 * @package App\Controller
 * @Route("/dashboard/feedback")
 */
class FeedbackController extends AbstractController
{
    use FileHelper;
    use RandomStringGenerator;
    use ServiceHelper;

    /**
     * @IsGranted({"ROLE_STUDENT_USER", "ROLE_EDUCATOR_USER"})
     * @Route("/request-lesson-experience-or-site-visit", name="request_lesson_experience_or_site_visit", options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requestIdeaAction(Request $request) {

        /** @var EducatorUser|StudentUser $user */
        $user = $this->getUser();

        $form = $this->createFormBuilder()
            ->add('message', TextareaType::class, ['label' => 'Request a lesson, experience, or site visit.',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $message = $form->get('message')->getData();

            foreach($user->getSchool()->getSchoolAdministrators() as $schoolAdministrator) {
                $this->feedbackMailer->requestForLessonIdeaOrSiteVisit($schoolAdministrator, $message);
            }

            $this->addFlash('success', 'Feedback successfully submitted.');
            return $this->redirectToRoute('request_lesson_experience_or_site_visit');
        }

        return $this->render('feedback/request_lesson_experience_or_site_visit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted({"ROLE_STUDENT_USER", "ROLE_EDUCATOR_USER"})
     * @Route("/experiences/{id}", name="experience_feedback", options = { "expose" = true })
     * @param Request $request
     * @param Experience $experience
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function experienceFeedbackAction(Request $request, Experience $experience) {

        /** @var EducatorUser|StudentUser $user */
        $user = $this->getUser();
        $feedback = null;
        $formType = null;
        $template = null;

        $experienceHasFeedback = $this->feedbackRepository->findOneBy([
           'user' => $user,
           'experience' => $experience
        ]) ? true : false;

        // look at the experience object and see which form you should load in
        switch ($experience->getClassName()) {
            case 'CompanyExperience':
                /** @var CompanyExperience $experience */
                if($user->isStudent()) {
                    $feedback = new StudentReviewCompanyExperienceFeedback();
                    $formType = StudentReviewCompanyExperienceFeedbackFormType::class;
                    $template = 'new_student_review_company_experience_feedback.html.twig';
                } elseif ($user->isEducator()) {
                    $feedback = new EducatorReviewCompanyExperienceFeedback();
                    $formType = EducatorReviewCompanyExperienceFeedbackFormType::class;
                    $template = 'new_educator_review_company_experience_feedback.html.twig';
                }
                break;
            case 'TeachLessonExperience':
                /** @var TeachLessonExperience $experience */
                if($user->isStudent()) {
                    $feedback = new StudentReviewTeachLessonExperienceFeedback();
                    $formType = StudentReviewTeachLessonExperienceFeedbackFormType::class;
                    $template = 'new_student_review_teach_lesson_experience_feedback.html.twig';
                } elseif ($user->isEducator()) {
                    $feedback = new EducatorReviewTeachLessonExperienceFeedback();
                    $formType = EducatorReviewTeachLessonExperienceFeedbackFormType::class;
                    $template = 'new_educator_review_teach_lesson_experience_feedback.html.twig';
                }
                break;
        }

        if(!$feedback || !$formType || !$template) {
            throw new \Exception("Form type, feedback, or template variables not found");
        }

        $form = $this->createForm($formType, $feedback);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            /** @var Feedback $feedback */
            $feedback = $form->getData();

            //todo we might not need the user and experience on each subclass of the feedback object
            $feedback->setUser($user);
            $feedback->setExperience($experience);

            switch ($feedback->getClassName()) {
                case 'EducatorReviewCompanyExperienceFeedback':
                    /** @var EducatorReviewCompanyExperienceFeedback $feedback */
                    /** @var CompanyExperience $experience */
                    $feedback->setCompanyExperience($experience);
                    $feedback->setEducator($user);

                    break;
                case 'EducatorReviewTeachLessonExperienceFeedback':
                    /** @var EducatorReviewTeachLessonExperienceFeedback $feedback */
                    /** @var TeachLessonExperience $experience */
                    $feedback->setTeachLessonExperience($experience);
                    $feedback->setEducator($user);
                    $feedback->setLesson($experience->getOriginalRequest()->getLesson());
                    break;
                case 'StudentReviewCompanyExperienceFeedback':
                    /** @var StudentReviewCompanyExperienceFeedback $feedback */
                    /** @var CompanyExperience $experience */
                    $feedback->setStudent($user);
                    $feedback->setCompanyExperience($experience);
                    break;
                case 'StudentReviewTeachLessonExperienceFeedback':
                    /** @var StudentReviewTeachLessonExperienceFeedback $feedback */
                    /** @var TeachLessonExperience $experience */
                    $feedback->setTeachLessonExperience($experience);
                    $feedback->setStudent($user);
                    $feedback->setLesson($experience->getOriginalRequest()->getLesson());
                    break;
            }

            $this->entityManager->persist($feedback);
            $this->entityManager->flush();

            $this->addFlash('success', 'Feedback successfully submitted.');
            return $this->redirectToRoute('dashboard');
        }

        return $this->render("feedback/{$template}", [
            'user' => $user,
            'form' => $form->createView(),
            'feedback' => $feedback,
            'experienceHasFeedback' => $experienceHasFeedback
        ]);
    }

    /**
     * @Route("/{id}/view", name="feedback_view", options = { "expose" = true })
     * @param Request $request
     * @param Feedback $feedback
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function feedbackViewAction(Request $request, Feedback $feedback) {

        $user = $this->getUser();
        $formType = null;
        $template = null;
        switch ($feedback->getClassName()) {
            case 'EducatorReviewCompanyExperienceFeedback':
                /** @var EducatorReviewCompanyExperienceFeedback $feedback */
                /** @var CompanyExperience $experience */
                $formType = EducatorReviewCompanyExperienceFeedbackFormType::class;
                $template = 'view_educator_review_company_experience_feedback.html.twig';
                break;
            case 'EducatorReviewTeachLessonExperienceFeedback':
                /** @var EducatorReviewTeachLessonExperienceFeedback $feedback */
                /** @var TeachLessonExperience $experience */
                $formType = EducatorReviewTeachLessonExperienceFeedbackFormType::class;
                $template = 'view_educator_review_teach_lesson_experience_feedback.html.twig';
                break;
            case 'StudentReviewCompanyExperienceFeedback':
                /** @var StudentReviewCompanyExperienceFeedback $feedback */
                /** @var CompanyExperience $experience */
                $formType = StudentReviewCompanyExperienceFeedbackFormType::class;
                $template = 'view_student_review_company_experience_feedback.html.twig';
                break;
            case 'StudentReviewTeachLessonExperienceFeedback':
                /** @var StudentReviewTeachLessonExperienceFeedback $feedback */
                /** @var TeachLessonExperience $experience */
                $formType = StudentReviewTeachLessonExperienceFeedbackFormType::class;
                $template = 'view_student_review_teach_lesson_experience_feedback.html.twig';
                break;
        }

        if(!$feedback || !$formType || !$template) {
            throw new \Exception("Form type, feedback, or template variables not found");
        }

        return $this->render("feedback/{$template}", [
            'user' => $user,
            'feedback' => $feedback,
        ]);
    }
}
