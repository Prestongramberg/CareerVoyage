<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\EducatorUser;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\RegionalCoordinatorRequest;
use App\Entity\SecondaryIndustry;
use App\Entity\StateCoordinator;
use App\Entity\StateCoordinatorRequest;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\NewLessonType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\RegionalCoordinatorFormType;
use App\Form\StateCoordinatorFormType;
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

            // I don't know any case where an educator user or a student user wouldn't have a site. Better safe than sorry
            if($user->getSite()) {
                foreach($user->getSite()->getSiteAdminUsers() as $siteAdminUser) {
                    $this->feedbackMailer->requestForLessonIdeaOrSiteVisit($siteAdminUser, $message);
                }
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
     * @Route("/events/{id}", name="event_feedback", options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function eventFeedbackAction(Request $request) {

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

            // I don't know any case where an educator user or a student user wouldn't have a site. Better safe than sorry
            if($user->getSite()) {
                foreach($user->getSite()->getSiteAdminUsers() as $siteAdminUser) {
                    $this->feedbackMailer->requestForLessonIdeaOrSiteVisit($siteAdminUser, $message);
                }
            }

            $this->addFlash('success', 'Feedback successfully submitted.');
            return $this->redirectToRoute('request_lesson_experience_or_site_visit');
        }

        return $this->render('feedback/request_lesson_experience_or_site_visit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}